<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Admin\Concerns\HandlesAdminCrud;
use App\Http\Controllers\Controller;
use App\Models\Client;
use App\Support\SortOrder;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\Rule;

class ClientController extends Controller
{
    use HandlesAdminCrud {
        mutateValidated as baseMutateValidated;
    }

    protected function modelClass(): string
    {
        return Client::class;
    }

    protected function indexQuery(Request $request): Builder
    {
        return Client::query()
            ->with(['category', 'categories', 'logo'])
            ->when($request->filled('client_category_id'), function (Builder $query) use ($request) {
                $categoryId = $request->integer('client_category_id');
                $query->whereHas('categories', fn (Builder $inner) => $inner->where('client_categories.id', $categoryId));
            })
            ->orderBy('sort_order');
    }

    protected function resolveModel(int|string $id): Model
    {
        return Client::query()->with(['category', 'categories', 'logo'])->findOrFail($id);
    }

    protected function storeRules(Request $request): array
    {
        return $this->rules();
    }

    protected function updateRules(Request $request, Model $model): array
    {
        return $this->rules((int) $model->id);
    }

    public function store(Request $request): JsonResponse
    {
        $validated = $this->mutateValidated($request, $request->validate($this->storeRules($request)));

        $client = DB::transaction(function () use ($validated) {
            $client = Client::create($this->payload($validated));
            $this->syncCategories($client, $validated['client_category_ids'] ?? []);

            return $client;
        });

        return response()->json($client->fresh(['category', 'categories', 'logo']), 201);
    }

    public function update(Request $request, int|string $id): JsonResponse
    {
        /** @var Client $client */
        $client = Client::query()->findOrFail($id);
        $validated = $this->mutateValidated($request, $request->validate($this->updateRules($request, $client)), $client);

        DB::transaction(function () use ($client, $validated) {
            $client->fill($this->payload($validated))->save();
            $this->syncCategories($client, $validated['client_category_ids'] ?? []);
        });

        return response()->json($client->fresh(['category', 'categories', 'logo']));
    }

    protected function rules(?int $ignoreId = null): array
    {
        return [
            'client_category_id' => ['nullable', 'integer', 'exists:client_categories,id'],
            'client_category_ids' => ['required', 'array', 'min:1'],
            'client_category_ids.*' => ['integer', 'exists:client_categories,id'],
            'name' => ['required', 'string', 'max:255'],
            'slug' => ['nullable', 'string', 'max:255', Rule::unique('clients', 'slug')->ignore($ignoreId)],
            'logo_media_id' => ['nullable', 'integer', 'exists:media_assets,id'],
            'website_url' => ['nullable', 'string', 'max:255'],
            'sort_order' => ['nullable'],
            'is_active' => ['nullable', 'boolean'],
            'show_on_home' => ['nullable', 'boolean'],
        ];
    }

    protected function mutateValidated(Request $request, array $validated, ?Model $model = null): array
    {
        $validated = $this->baseMutateValidated($request, $validated, $model);
        $validated['client_category_ids'] = array_values(array_unique(array_map('intval', $validated['client_category_ids'] ?? [])));

        if (! isset($validated['client_category_id']) && ! empty($validated['client_category_ids'])) {
            $validated['client_category_id'] = $validated['client_category_ids'][0];
        }

        return $validated;
    }

    protected function payload(array $validated): array
    {
        return collect($validated)->only([
            'client_category_id',
            'name',
            'slug',
            'logo_media_id',
            'website_url',
            'sort_order',
            'is_active',
            'show_on_home',
        ])->all();
    }

    protected function syncCategories(Client $client, array $categoryIds): void
    {
        $sync = [];

        foreach (array_values($categoryIds) as $index => $categoryId) {
            $sync[$categoryId] = [
                'sort_order' => SortOrder::fromPosition($index + 1),
            ];
        }

        $client->categories()->sync($sync);

        if (! empty($categoryIds)) {
            $client->forceFill(['client_category_id' => $categoryIds[0]])->saveQuietly();
        }
    }
}
