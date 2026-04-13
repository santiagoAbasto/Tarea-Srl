<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Admin\Concerns\HandlesAdminCrud;
use App\Http\Controllers\Controller;
use App\Models\PostCategory;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;

class PostCategoryController extends Controller
{
    use HandlesAdminCrud;

    protected function modelClass(): string
    {
        return PostCategory::class;
    }

    protected function indexQuery(Request $request): Builder
    {
        return PostCategory::query()->withCount('posts')->orderBy('sort_order');
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
            'slug' => ['nullable', 'string', 'max:255', Rule::unique('post_categories', 'slug')->ignore($ignoreId)],
            'color' => ['nullable', 'string', 'max:50'],
            'sort_order' => ['nullable'],
            'is_active' => ['nullable', 'boolean'],
        ];
    }
}
