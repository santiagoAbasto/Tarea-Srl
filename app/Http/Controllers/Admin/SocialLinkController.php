<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Admin\Concerns\HandlesAdminCrud;
use App\Http\Controllers\Controller;
use App\Models\SocialLink;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Http\Request;

class SocialLinkController extends Controller
{
    use HandlesAdminCrud;

    protected function modelClass(): string
    {
        return SocialLink::class;
    }

    protected function indexQuery(Request $request): Builder
    {
        return SocialLink::query()
            ->when($request->filled('location'), fn (Builder $query) => $query->where('location', $request->string('location')))
            ->orderBy('sort_order');
    }

    protected function storeRules(Request $request): array
    {
        return $this->rules();
    }

    protected function updateRules(Request $request, \Illuminate\Database\Eloquent\Model $model): array
    {
        return $this->rules();
    }

    protected function rules(): array
    {
        return [
            'platform' => ['required', 'string', 'max:100'],
            'label' => ['nullable', 'string', 'max:255'],
            'url' => ['required', 'string', 'max:255'],
            'location' => ['nullable', 'string', 'max:50'],
            'icon' => ['nullable', 'string', 'max:255'],
            'sort_order' => ['nullable'],
            'is_active' => ['nullable', 'boolean'],
        ];
    }
}
