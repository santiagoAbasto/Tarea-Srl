<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Admin\Concerns\HandlesAdminCrud;
use App\Http\Controllers\Controller;
use App\Models\ProductFamily;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;

class ProductFamilyController extends Controller
{
    use HandlesAdminCrud;

    protected function modelClass(): string
    {
        return ProductFamily::class;
    }

    protected function indexQuery(Request $request): Builder
    {
        return ProductFamily::query()->with(['coverMedia', 'bannerMedia'])->orderBy('sort_order');
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
            'slug' => ['nullable', 'string', 'max:255', Rule::unique('product_families', 'slug')->ignore($ignoreId)],
            'description' => ['nullable', 'string'],
            'cover_media_id' => ['nullable', 'integer', 'exists:media_assets,id'],
            'banner_media_id' => ['nullable', 'integer', 'exists:media_assets,id'],
            'accent_color' => ['nullable', 'string', 'max:50'],
            'sort_order' => ['nullable'],
            'is_active' => ['nullable', 'boolean'],
            'show_on_products_page' => ['nullable', 'boolean'],
            'show_on_home' => ['nullable', 'boolean'],
        ];
    }
}
