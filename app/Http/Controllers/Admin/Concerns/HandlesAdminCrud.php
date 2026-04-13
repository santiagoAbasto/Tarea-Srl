<?php

namespace App\Http\Controllers\Admin\Concerns;

use App\Support\SortOrder;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Str;
use App\Support\CmsSecurity;

trait HandlesAdminCrud
{
    protected int $perPage = 15;

    abstract protected function modelClass(): string;

    protected function indexQuery(Request $request): Builder
    {
        /** @var Model $model */
        $model = app($this->modelClass());

        return $model->newQuery();
    }

    protected function storeRules(Request $request): array
    {
        return [];
    }

    protected function updateRules(Request $request, Model $model): array
    {
        return [];
    }

    protected function mutateValidated(Request $request, array $validated, ?Model $model = null): array
    {
        $validated = $this->sanitizeValidated($validated);

        if (array_key_exists('name', $validated) && ! array_key_exists('slug', $validated)) {
            $validated['slug'] = Str::slug((string) $validated['name']);
        }

        if (array_key_exists('sort_order', $validated)) {
            $validated['sort_order'] = SortOrder::normalize($validated['sort_order']);
        } elseif ($model === null) {
            /** @var Model $instance */
            $instance = app($this->modelClass());

            if (in_array('sort_order', $instance->getFillable(), true)) {
                $validated['sort_order'] = SortOrder::next(
                    $instance->newQuery()->pluck('sort_order')->all()
                );
            }
        }

        return $validated;
    }

    protected function sanitizeValidated(array $validated): array
    {
        foreach ($validated as $key => $value) {
            if (is_array($value)) {
                $validated[$key] = cms_sanitize_array($value);
                continue;
            }

            if (! is_string($value)) {
                continue;
            }

            $validated[$key] = $this->sanitizeFieldValue($key, $value);
        }

        return $validated;
    }

    protected function sanitizeFieldValue(string $key, string $value): mixed
    {
        $normalizedKey = Str::lower($key);

        if ($normalizedKey === 'password') {
            return $value;
        }

        if ($normalizedKey === 'email' || Str::endsWith($normalizedKey, '_email')) {
            return cms_email($value);
        }

        if ($normalizedKey === 'phone' || Str::contains($normalizedKey, 'phone')) {
            return cms_phone($value);
        }

        if ($normalizedKey === 'description'
            || $normalizedKey === 'content'
            || $normalizedKey === 'body'
            || $normalizedKey === 'excerpt'
            || $normalizedKey === 'summary'
            || $normalizedKey === 'material'
            || $normalizedKey === 'treatment'
            || $normalizedKey === 'observations'
        ) {
            return cms_rich_text($value);
        }

        if ($normalizedKey === 'url'
            || $normalizedKey === 'link'
            || Str::endsWith($normalizedKey, '_url')
        ) {
            return CmsSecurity::sanitizeUrl($value, true);
        }

        return cms_plain_text($value);
    }

    protected function afterStore(Model $model, Request $request): void
    {
    }

    protected function afterUpdate(Model $model, Request $request): void
    {
    }

    public function index(Request $request): JsonResponse
    {
        $perPage = max(1, min(100, (int) $request->integer('per_page', $this->perPage)));

        return response()->json(
            $this->indexQuery($request)->paginate($perPage)->withQueryString()
        );
    }

    protected function resolveModel(int|string $id): Model
    {
        return $this->modelClass()::query()->findOrFail($id);
    }

    public function show(int|string $id): JsonResponse
    {
        $model = $this->resolveModel($id);

        return response()->json($model);
    }

    public function store(Request $request): JsonResponse
    {
        $validated = $request->validate($this->storeRules($request));
        $validated = $this->mutateValidated($request, $validated);

        /** @var Model $model */
        $model = $this->modelClass()::create($validated);

        $this->afterStore($model, $request);

        return response()->json($model->fresh(), 201);
    }

    public function update(Request $request, int|string $id): JsonResponse
    {
        $model = $this->resolveModel($id);
        $validated = $request->validate($this->updateRules($request, $model));
        $validated = $this->mutateValidated($request, $validated, $model);

        $model->fill($validated)->save();

        $this->afterUpdate($model, $request);

        return response()->json($model->fresh());
    }

    public function destroy(int|string $id): JsonResponse
    {
        $model = $this->resolveModel($id);
        $model->delete();

        return response()->json(['deleted' => true]);
    }
}
