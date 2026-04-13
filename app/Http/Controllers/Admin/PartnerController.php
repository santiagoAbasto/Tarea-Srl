<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Admin\Concerns\HandlesAdminCrud;
use App\Http\Controllers\Controller;
use App\Models\Partner;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;

class PartnerController extends Controller
{
    use HandlesAdminCrud;

    protected function modelClass(): string
    {
        return Partner::class;
    }

    protected function indexQuery(Request $request): Builder
    {
        return Partner::query()
            ->with('logo')
            ->when($request->filled('partner_type'), fn (Builder $query) => $query->where('partner_type', $request->string('partner_type')))
            ->orderBy('sort_order');
    }

    protected function storeRules(Request $request): array
    {
        return $this->rules();
    }

    protected function updateRules(Request $request, \Illuminate\Database\Eloquent\Model $model): array
    {
        return $this->rules((int) $model->id);
    }

    protected function rules(?int $ignoreId = null): array
    {
        return [
            'name' => ['required', 'string', 'max:255'],
            'slug' => ['nullable', 'string', 'max:255', Rule::unique('partners', 'slug')->ignore($ignoreId)],
            'logo_media_id' => ['nullable', 'integer', 'exists:media_assets,id'],
            'website_url' => ['nullable', 'string', 'max:255'],
            'partner_type' => ['required', 'string', 'max:50'],
            'sort_order' => ['nullable'],
            'is_active' => ['nullable', 'boolean'],
            'show_on_home' => ['nullable', 'boolean'],
            'show_on_page' => ['nullable', 'boolean'],
        ];
    }
}
