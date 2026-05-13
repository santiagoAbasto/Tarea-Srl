<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Product;
use App\Models\ProductMedia;
use App\Models\ProductRelated;
use App\Models\ProductSpecColumn;
use App\Models\ProductSpecRow;
use App\Models\ProductSpecTable;
use App\Models\ProductSpecValue;
use App\Support\SortOrder;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;
use Illuminate\Validation\Rule;

class ProductController extends Controller
{
    public function index(Request $request): JsonResponse
    {
        $products = Product::query()
            ->with(['family', 'subfamily', 'mainMedia', 'brandLogoMedia', 'technicalSheet'])
            ->when($request->filled('product_family_id'), fn (Builder $query) => $query->where('product_family_id', $request->integer('product_family_id')))
            ->when($request->filled('product_subfamily_id'), fn (Builder $query) => $query->where('product_subfamily_id', $request->integer('product_subfamily_id')))
            ->orderBy('sort_order')
            ->paginate(max(1, min(100, $request->integer('per_page', 15))))
            ->withQueryString();

        return response()->json($products);
    }

    public function show(int|string $id): JsonResponse
    {
        return response()->json($this->serializeProduct($this->findProduct($id)));
    }

    public function store(Request $request): JsonResponse
    {
        $validated = $request->validate($this->rules());

        $product = DB::transaction(function () use ($validated) {
            $product = Product::create($this->payload($validated));
            $this->syncMedia($product, $validated['media'] ?? []);
            $this->syncSpecTables($product, $validated['spec_tables'] ?? []);
            $this->syncRelated($product, $validated['related_product_ids'] ?? []);

            return $product;
        });

        return response()->json($this->serializeProduct($this->findProduct($product->id)), 201);
    }

    public function update(Request $request, int|string $id): JsonResponse
    {
        $product = Product::query()->findOrFail($id);
        $validated = $request->validate($this->rules((int) $product->id));

        DB::transaction(function () use ($product, $validated) {
            $product->fill($this->payload($validated, $product))->save();
            $this->syncMedia($product, $validated['media'] ?? []);
            $this->syncSpecTables($product, $validated['spec_tables'] ?? []);
            $this->syncRelated($product, $validated['related_product_ids'] ?? []);
        });

        return response()->json($this->serializeProduct($this->findProduct($product->id)));
    }

    public function destroy(int|string $id): JsonResponse
    {
        Product::query()->findOrFail($id)->delete();

        return response()->json(['deleted' => true]);
    }

    protected function findProduct(int|string $id): Product
    {
        return Product::query()
            ->with([
                'family',
                'subfamily',
                'mainMedia',
                'brandLogoMedia',
                'technicalSheet',
                'media.media',
                'specTables.columns',
                'specTables.rows.values.column',
                'relatedProducts.mainMedia',
            ])
            ->findOrFail($id);
    }

    protected function serializeProduct(Product $product): array
    {
        return [
            'id' => $product->id,
            'product_family_id' => $product->product_family_id,
            'product_subfamily_id' => $product->product_subfamily_id,
            'name' => $product->name,
            'slug' => $product->slug,
            'sku' => $product->sku,
            'short_description' => $product->short_description,
            'description' => $product->description,
            'material' => $product->material,
            'treatment' => $product->treatment,
            'observations' => $product->observations,
            'main_media_id' => $product->main_media_id,
            'brand_logo_media_id' => $product->brand_logo_media_id,
            'technical_sheet_media_id' => $product->technical_sheet_media_id,
            'sort_order' => $product->sort_order,
            'is_active' => $product->is_active,
            'is_featured_home' => $product->is_featured_home,
            'is_featured_family' => $product->is_featured_family,
            'main_media' => $product->mainMedia,
            'brand_logo_media' => $product->brandLogoMedia,
            'technical_sheet' => $product->technicalSheet,
            'media' => $product->media->map(fn (ProductMedia $item) => [
                'id' => $item->id,
                'media_id' => $item->media_id,
                'sort_order' => $item->sort_order,
                'is_primary' => $item->is_primary,
                'media' => $item->media,
            ])->values()->all(),
            'related_products' => $product->relatedProducts->map(fn (Product $related) => [
                'id' => $related->id,
                'name' => $related->name,
                'slug' => $related->slug,
                'main_media' => $related->mainMedia,
            ])->values()->all(),
            'spec_tables' => $product->specTables->map(function (ProductSpecTable $table) {
                $columns = $table->columns->sortBy('sort_order')->values();

                return [
                    'id' => $table->id,
                    'title' => $table->title,
                    'sort_order' => $table->sort_order,
                    'columns' => $columns->map(fn (ProductSpecColumn $column) => [
                        'id' => $column->id,
                        'label' => $column->label,
                        'unit' => $column->unit,
                        'sort_order' => $column->sort_order,
                    ])->values()->all(),
                    'rows' => $table->rows->sortBy('sort_order')->values()->map(function (ProductSpecRow $row) use ($columns) {
                        $valuesByColumn = $row->values->keyBy('product_spec_column_id');

                        return [
                            'id' => $row->id,
                            'sort_order' => $row->sort_order,
                            'values' => $columns->map(
                                fn (ProductSpecColumn $column) => (string) optional($valuesByColumn->get($column->id))->value
                            )->all(),
                        ];
                    })->all(),
                ];
            })->values()->all(),
        ];
    }

    protected function rules(?int $ignoreId = null): array
    {
        return [
            'product_family_id' => ['required', 'integer', 'exists:product_families,id'],
            'product_subfamily_id' => ['nullable', 'integer', 'exists:product_subfamilies,id'],
            'name' => ['required', 'string', 'max:255'],
            'slug' => ['nullable', 'string', 'max:255', Rule::unique('products', 'slug')->ignore($ignoreId)],
            'sku' => ['nullable', 'string', 'max:255'],
            'short_description' => ['nullable', 'string', 'max:255'],
            'description' => ['nullable', 'string'],
            'material' => ['nullable', 'string'],
            'treatment' => ['nullable', 'string'],
            'observations' => ['nullable', 'string'],
            'main_media_id' => ['nullable', 'integer', 'exists:media_assets,id'],
            'brand_logo_media_id' => ['nullable', 'integer', 'exists:media_assets,id'],
            'technical_sheet_media_id' => ['nullable', 'integer', 'exists:media_assets,id'],
            'sort_order' => ['nullable'],
            'is_active' => ['nullable', 'boolean'],
            'is_featured_home' => ['nullable', 'boolean'],
            'is_featured_family' => ['nullable', 'boolean'],
            'media' => ['nullable', 'array'],
            'media.*.media_id' => ['required', 'integer', 'exists:media_assets,id'],
            'media.*.sort_order' => ['nullable'],
            'media.*.is_primary' => ['nullable', 'boolean'],
            'spec_tables' => ['nullable', 'array'],
            'spec_tables.*.title' => ['nullable', 'string', 'max:255'],
            'spec_tables.*.sort_order' => ['nullable'],
            'spec_tables.*.columns' => ['nullable', 'array'],
            'spec_tables.*.columns.*.label' => ['required', 'string', 'max:255'],
            'spec_tables.*.columns.*.unit' => ['nullable', 'string', 'max:50'],
            'spec_tables.*.columns.*.sort_order' => ['nullable'],
            'spec_tables.*.rows' => ['nullable', 'array'],
            'spec_tables.*.rows.*.sort_order' => ['nullable'],
            'spec_tables.*.rows.*.values' => ['nullable', 'array'],
            'related_product_ids' => ['nullable', 'array'],
            'related_product_ids.*' => ['integer', 'exists:products,id'],
        ];
    }

    protected function payload(array $validated, ?Product $product = null): array
    {
        if (! array_key_exists('slug', $validated) || blank($validated['slug'])) {
            $validated['slug'] = Str::slug((string) $validated['name']);
        }

        if (array_key_exists('sort_order', $validated) || $product === null) {
            $validated['sort_order'] = SortOrder::normalize(
                $validated['sort_order'] ?? null,
                Product::query()->count() + 1
            );
        }

        return collect($validated)->only([
            'product_family_id',
            'product_subfamily_id',
            'name',
            'slug',
            'sku',
            'short_description',
            'description',
            'material',
            'treatment',
            'observations',
            'main_media_id',
            'brand_logo_media_id',
            'technical_sheet_media_id',
            'sort_order',
            'is_active',
            'is_featured_home',
            'is_featured_family',
        ])->all();
    }

    protected function syncMedia(Product $product, array $rows): void
    {
        $product->media()->delete();

        foreach (array_values($rows) as $index => $row) {
            ProductMedia::create([
                'product_id' => $product->id,
                'media_id' => $row['media_id'],
                'sort_order' => SortOrder::normalize($row['sort_order'] ?? null, $index + 1),
                'is_primary' => $row['is_primary'] ?? false,
            ]);
        }
    }

    protected function syncSpecTables(Product $product, array $tables): void
    {
        $product->specTables()->delete();

        foreach (array_values($tables) as $tableIndex => $tableRow) {
            $table = ProductSpecTable::create([
                'product_id' => $product->id,
                'title' => $tableRow['title'] ?? null,
                'sort_order' => SortOrder::normalize($tableRow['sort_order'] ?? null, $tableIndex + 1),
            ]);

            $columnIds = [];
            foreach (array_values($tableRow['columns'] ?? []) as $columnIndex => $columnRow) {
                $column = ProductSpecColumn::create([
                    'product_spec_table_id' => $table->id,
                    'label' => $columnRow['label'],
                    'unit' => $columnRow['unit'] ?? null,
                    'sort_order' => SortOrder::normalize($columnRow['sort_order'] ?? null, $columnIndex + 1),
                ]);

                $columnIds[] = $column->id;
            }

            foreach (array_values($tableRow['rows'] ?? []) as $rowIndex => $rowRow) {
                $row = ProductSpecRow::create([
                    'product_spec_table_id' => $table->id,
                    'sort_order' => SortOrder::normalize($rowRow['sort_order'] ?? null, $rowIndex + 1),
                ]);

                foreach (($rowRow['values'] ?? []) as $index => $value) {
                    if (! isset($columnIds[$index])) {
                        continue;
                    }

                    ProductSpecValue::create([
                        'product_spec_row_id' => $row->id,
                        'product_spec_column_id' => $columnIds[$index],
                        'value' => $value,
                    ]);
                }
            }
        }
    }

    protected function syncRelated(Product $product, array $relatedIds): void
    {
        ProductRelated::query()->where('product_id', $product->id)->delete();

        foreach (array_values(array_unique($relatedIds)) as $index => $relatedId) {
            if ((int) $relatedId === (int) $product->id) {
                continue;
            }

            ProductRelated::create([
                'product_id' => $product->id,
                'related_product_id' => $relatedId,
                'sort_order' => SortOrder::fromPosition($index + 1),
            ]);
        }
    }
}
