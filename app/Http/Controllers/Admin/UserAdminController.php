<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\User;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Storage;
use Illuminate\Validation\Rule;
use Inertia\Inertia;
use Inertia\Response;

class UserAdminController extends Controller
{
    public function index(): Response
    {
        $principalId = $this->principalUserId();

        return Inertia::render('Admin/Users/Index', [
            'principalId' => $principalId,
            'users' => User::query()
                ->orderBy('id')
                ->get()
                ->map(function (User $user) use ($principalId): array {
                    return [
                        'id' => $user->id,
                        'name' => $user->name,
                        'email' => $user->email,
                        'avatar_url' => $user->avatar_url,
                        'can_access_admin' => (bool) $user->can_access_admin,
                        'is_principal' => $user->id === $principalId,
                        'created_at' => optional($user->created_at)?->format('d/m/Y H:i'),
                    ];
                })
                ->values(),
        ]);
    }

    public function store(Request $request): RedirectResponse
    {
        $data = $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'email' => ['required', 'string', 'email', 'max:255', 'unique:users,email'],
            'password' => ['required', 'string', 'min:8', 'confirmed'],
            'avatar' => ['nullable', 'image', 'mimes:jpg,jpeg,png,webp,gif', 'max:5120'],
            'can_access_admin' => ['nullable', 'boolean'],
        ]);

        $user = new User();
        $user->name = cms_plain_text($data['name']) ?? '';
        $user->email = cms_email($data['email']) ?? '';
        $user->password = Hash::make($data['password']);
        $user->can_access_admin = $request->boolean('can_access_admin', true);

        if ($request->hasFile('avatar')) {
            $user->avatar = $request->file('avatar')->store('avatars', 'public');
        }

        $user->save();

        cms_security_log('notice', 'Admin user created.', [
            'target_user_id' => $user->id,
            'target_email' => $user->email,
            'granted_admin_access' => (bool) $user->can_access_admin,
        ], $request);

        return back()->with('success', 'Usuario creado correctamente.');
    }

    public function update(Request $request, User $user): RedirectResponse
    {
        $principalId = $this->principalUserId();

        $data = $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'email' => ['required', 'string', 'email', 'max:255', Rule::unique('users', 'email')->ignore($user->id)],
            'password' => ['nullable', 'string', 'min:8', 'confirmed'],
            'avatar' => ['nullable', 'image', 'mimes:jpg,jpeg,png,webp,gif', 'max:5120'],
            'remove_avatar' => ['nullable', 'boolean'],
            'can_access_admin' => ['nullable', 'boolean'],
        ]);

        $originalAdminAccess = (bool) $user->can_access_admin;
        $user->name = cms_plain_text($data['name']) ?? '';
        $user->email = cms_email($data['email']) ?? '';
        $user->can_access_admin = $user->id === $principalId
            ? true
            : $request->boolean('can_access_admin', $user->can_access_admin);

        if (! empty($data['password'])) {
            $user->password = Hash::make($data['password']);
        }

        if ($request->boolean('remove_avatar') && $user->avatar) {
            Storage::disk('public')->delete($user->avatar);
            $user->avatar = null;
        }

        if ($request->hasFile('avatar')) {
            if ($user->avatar) {
                Storage::disk('public')->delete($user->avatar);
            }

            $user->avatar = $request->file('avatar')->store('avatars', 'public');
        }

        $user->save();

        cms_security_log('notice', 'Admin user updated.', [
            'target_user_id' => $user->id,
            'target_email' => $user->email,
            'admin_access_changed' => $originalAdminAccess !== (bool) $user->can_access_admin,
            'granted_admin_access' => (bool) $user->can_access_admin,
            'password_updated' => ! empty($data['password']),
            'avatar_replaced' => $request->hasFile('avatar'),
            'avatar_removed' => $request->boolean('remove_avatar'),
        ], $request);

        return back()->with('success', 'Usuario actualizado correctamente.');
    }

    public function destroy(User $user): RedirectResponse
    {
        if ((int) request()->user()?->id === (int) $user->id) {
            return back()->with('error', 'No puedes eliminar tu propio usuario mientras estás logueado.');
        }

        if ($user->id === $this->principalUserId()) {
            return back()->with('error', 'El usuario principal no puede eliminarse.');
        }

        if ($user->avatar) {
            Storage::disk('public')->delete($user->avatar);
        }

        cms_security_log('notice', 'Admin user deleted.', [
            'target_user_id' => $user->id,
            'target_email' => $user->email,
        ], request());

        $user->delete();

        return back()->with('success', 'Usuario eliminado correctamente.');
    }

    private function principalUserId(): ?int
    {
        return User::query()->orderBy('id')->value('id');
    }
}
