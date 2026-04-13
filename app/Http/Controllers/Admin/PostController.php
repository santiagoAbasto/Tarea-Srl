<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Admin\Concerns\HandlesAdminCrud;
use App\Http\Controllers\Controller;
use App\Models\Post;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;

class PostController extends Controller
{
    use HandlesAdminCrud;

    protected function modelClass(): string
    {
        return Post::class;
    }

    protected function indexQuery(Request $request): Builder
    {
        return Post::query()
            ->with(['category', 'coverMedia'])
            ->when($request->filled('post_category_id'), fn (Builder $query) => $query->where('post_category_id', $request->integer('post_category_id')))
            ->orderByDesc('published_at')
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
            'post_category_id' => ['nullable', 'integer', 'exists:post_categories,id'],
            'title' => ['required', 'string', 'max:255'],
            'slug' => ['nullable', 'string', 'max:255', Rule::unique('posts', 'slug')->ignore($ignoreId)],
            'excerpt' => ['nullable', 'string'],
            'content' => ['nullable', 'string'],
            'cover_media_id' => ['nullable', 'integer', 'exists:media_assets,id'],
            'author_name' => ['nullable', 'string', 'max:255'],
            'published_at' => ['nullable', 'date'],
            'sort_order' => ['nullable'],
            'is_active' => ['nullable', 'boolean'],
            'show_on_home' => ['nullable', 'boolean'],
            'is_featured' => ['nullable', 'boolean'],
            'seo_title' => ['nullable', 'string', 'max:255'],
            'seo_description' => ['nullable', 'string'],
        ];
    }
}
