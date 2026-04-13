<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Admin\Concerns\HandlesAdminCrud;
use App\Http\Controllers\Controller;
use App\Models\ProductSubfamily;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;

class ProductSubfamilyController extends Controller
{
    use HandlesAdminCrud;

    protected function modelClass(): string
    {
        return ProductSubfamily::class;
    }

    protected function indexQuery(Request $request): Builder
    {
        return ProductSubfamily::query()
            ->with(['family', 'coverMedia'])
            ->when($request->filled('product_family_id'), fn (Builder $query) => $query->where('product_family_id', $request->integer('product_family_id')))
            ->orderBy('sort_order');
    }

    protected function storeRules(Request $request): array
    {
        return $this->rules();
    }

    protected function updateRules(Request $request, \Illuminate\Database\Eloquent\Model $model): array
    {
        return $this->rules((int) $model->id, (int) $model->product_family_id);
    }

    protected function rules(?int $ignoreId = null, ?int $familyId = null): array
    {
        $familyId = request()->integer('product_family_id', $familyId);

        return [
            'product_family_id' => ['required', 'integer', 'exists:product_families,id'],
            'name' => ['required', 'string', 'max:255'],
            'short_description' => ['nullable', 'string', 'max:255'],
            'slug' => [
                'nullable',
                'string',
                'max:255',
                Rule::unique('product_subfamilies', 'slug')
                    ->where(fn ($query) => $query->where('product_family_id', $familyId))
                    ->ignore($ignoreId),
            ],
            'description' => ['nullable', 'string'],
            'cover_media_id' => ['nullable', 'integer', 'exists:media_assets,id'],
            'accent_color' => ['nullable', 'string', 'max:50'],
            'sort_order' => ['nullable'],
            'is_active' => ['nullable', 'boolean'],
            'show_on_home' => ['nullable', 'boolean'],
            'show_on_family_page' => ['nullable', 'boolean'],
        ];
    }
}
