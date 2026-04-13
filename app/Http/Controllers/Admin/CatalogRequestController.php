<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Admin\Concerns\HandlesAdminCrud;
use App\Http\Controllers\Controller;
use App\Models\CatalogRequest;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Http\Request;

class CatalogRequestController extends Controller
{
    use HandlesAdminCrud;

    protected function modelClass(): string
    {
        return CatalogRequest::class;
    }

    protected function indexQuery(Request $request): Builder
    {
        return CatalogRequest::query()->orderByDesc('created_at');
    }

    protected function storeRules(Request $request): array
    {
        return [
            'name' => ['required', 'string', 'max:255'],
            'email' => ['required', 'email'],
            'company' => ['nullable', 'string', 'max:255'],
            'phone' => ['nullable', 'string', 'max:255'],
            'message' => ['nullable', 'string'],
            'is_read' => ['nullable', 'boolean'],
            'status' => ['nullable', 'string', 'max:50'],
        ];
    }

    protected function updateRules(Request $request, \Illuminate\Database\Eloquent\Model $model): array
    {
        return $this->storeRules($request);
    }
}
