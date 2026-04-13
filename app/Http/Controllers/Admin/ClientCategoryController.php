<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Admin\Concerns\HandlesAdminCrud;
use App\Http\Controllers\Controller;
use App\Models\ClientCategory;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;

class ClientCategoryController extends Controller
{
    use HandlesAdminCrud;

    protected function modelClass(): string
    {
        return ClientCategory::class;
    }

    protected function indexQuery(Request $request): Builder
    {
        return ClientCategory::query()->withCount('clients')->orderBy('sort_order');
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
            'slug' => ['nullable', 'string', 'max:255', Rule::unique('client_categories', 'slug')->ignore($ignoreId)],
            'sort_order' => ['nullable'],
            'is_active' => ['nullable', 'boolean'],
        ];
    }
}
