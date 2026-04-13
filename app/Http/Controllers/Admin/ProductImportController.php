<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\MediaAsset;
use App\Models\Product;
use App\Models\ProductFamily;
use App\Models\ProductMedia;
use App\Models\ProductRelated;
use App\Models\ProductSpecColumn;
use App\Models\ProductSpecRow;
use App\Models\ProductSpecTable;
use App\Models\ProductSpecValue;
use App\Models\ProductSubfamily;
use App\Support\SortOrder;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;
use PhpOffice\PhpSpreadsheet\Cell\Coordinate;
use PhpOffice\PhpSpreadsheet\IOFactory;
use PhpOffice\PhpSpreadsheet\Spreadsheet;
use Symfony\Component\HttpFoundation\StreamedResponse;

class ProductImportController extends Controller
{
    public function import(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'file' => ['required', 'file', 'mimes:xlsx,xls,csv,txt'],
        ]);

        $sheets = $this->readWorkbook($validated['file']->getRealPath());

        $familiesRows = $sheets['families'] ?? [];
        $subfamiliesRows = $sheets['subfamilies'] ?? [];
        $productsRows = $sheets['products'] ?? [];

        if (empty($familiesRows) && empty($subfamiliesRows) && empty($productsRows)) {
            return response()->json([
                'message' => 'El archivo no tiene hojas válidas. Usá families, subfamilies y products.',
            ], 422);
        }

        $summary = DB::transaction(function () use ($familiesRows, $subfamiliesRows, $productsRows) {
            $familyCount = 0;
            $subfamilyCount = 0;
            $productCount = 0;
            $pendingProductSync = [];

            foreach ($familiesRows as $rowIndex => $row) {
                if ($this->rowIsEmpty($row)) {
                    continue;
                }

                $this->upsertFamily($row, $rowIndex + 2);
                $familyCount++;
            }

            foreach ($subfamiliesRows as $rowIndex => $row) {
                if ($this->rowIsEmpty($row)) {
                    continue;
                }

                $this->upsertSubfamily($row, $rowIndex + 2);
                $subfamilyCount++;
            }

            foreach ($productsRows as $rowIndex => $row) {
                if ($this->rowIsEmpty($row)) {
                    continue;
                }

                $pendingProductSync[] = $this->upsertProductCore($row, $rowIndex + 2);
                $productCount++;
            }

            foreach ($pendingProductSync as $pending) {
                $this->syncImportedProduct($pending['product'], $pending['row'], $pending['row_number']);
            }

            return [
                'families' => $familyCount,
                'subfamilies' => $subfamilyCount,
                'products' => $productCount,
            ];
        });

        return response()->json([
            'message' => 'La importación masiva de productos se completó correctamente.',
            'summary' => $summary,
        ]);
    }

    public function template(): StreamedResponse
    {
        $spreadsheet = new Spreadsheet();

        $familiesSheet = $spreadsheet->getActiveSheet();
        $familiesSheet->setTitle('families');
        $familiesSheet->fromArray([[
            'name',
            'slug',
            'description',
            'accent_color',
            'sort_order',
            'is_active',
            'show_on_products_page',
            'show_on_home',
            'cover_media_id',
            'banner_media_id',
        ]], null, 'A1');

        $subfamiliesSheet = $spreadsheet->createSheet();
        $subfamiliesSheet->setTitle('subfamilies');
        $subfamiliesSheet->fromArray([[
            'family_slug',
            'name',
            'slug',
            'short_description',
            'description',
            'accent_color',
            'sort_order',
            'is_active',
            'show_on_home',
            'show_on_family_page',
        ]], null, 'A1');

        $productsSheet = $spreadsheet->createSheet();
        $productsSheet->setTitle('products');
        $productsSheet->fromArray([[
            'family_slug',
            'subfamily_slug',
            'name',
            'slug',
            'sku',
            'short_description',
            'description',
            'material',
            'treatment',
            'observations',
            'sort_order',
            'is_active',
            'is_featured_home',
            'is_featured_family',
            'main_media_id',
            'technical_sheet_media_id',
            'gallery_media_ids',
            'related_product_slugs',
            'spec_tables_json',
        ]], null, 'A1');

        foreach ($spreadsheet->getAllSheets() as $sheet) {
            $highestColumn = $sheet->getHighestColumn();
            $highestColumnIndex = Coordinate::columnIndexFromString($highestColumn);

            for ($column = 1; $column <= $highestColumnIndex; $column++) {
                $sheet->getColumnDimension(Coordinate::stringFromColumnIndex($column))->setAutoSize(true);
            }
        }

        return response()->streamDownload(function () use ($spreadsheet) {
            $writer = IOFactory::createWriter($spreadsheet, 'Xlsx');
            $writer->save('php://output');
        }, 'template-importacion-productos.xlsx', [
            'Content-Type' => 'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet',
        ]);
    }

    private function readWorkbook(string $path): array
    {
        $spreadsheet = IOFactory::load($path);
        $sheets = [];

        foreach ($spreadsheet->getWorksheetIterator() as $sheet) {
            $rows = $sheet->toArray(null, true, true, false);
            $normalizedRows = $this->rowsWithHeadings($rows);

            $key = Str::of($sheet->getTitle())->lower()->snake()->toString();
            $sheets[$key] = $normalizedRows;
        }

        if (count($sheets) === 1 && ! isset($sheets['products'])) {
            $sheets['products'] = array_values($sheets)[0];
        }

        return $sheets;
    }

    private function rowsWithHeadings(array $rows): array
    {
        $headingRow = array_shift($rows) ?? [];
        $headings = array_map(fn ($heading) => Str::of((string) $heading)->lower()->snake()->toString(), $headingRow);

        return collect($rows)
            ->map(function (array $row) use ($headings) {
                $normalized = [];

                foreach ($headings as $index => $heading) {
                    if ($heading === '') {
                        continue;
                    }

                    $normalized[$heading] = $row[$index] ?? null;
                }

                return $normalized;
            })
            ->values()
            ->all();
    }

    private function rowIsEmpty(array $row): bool
    {
        foreach ($row as $value) {
            if (trim((string) $value) !== '') {
                return false;
            }
        }

        return true;
    }

    private function upsertFamily(array $row, int $rowNumber): ProductFamily
    {
        $name = $this->requiredCell($row, 'name', 'families', $rowNumber);
        $slug = $this->slugValue($row['slug'] ?? null, $name);

        return ProductFamily::query()->updateOrCreate(
            ['slug' => $slug],
            [
                'name' => $name,
                'description' => $this->nullableString($row['description'] ?? null),
                'accent_color' => $this->nullableString($row['accent_color'] ?? null),
                'sort_order' => SortOrder::normalize($row['sort_order'] ?? null, ProductFamily::query()->count() + 1),
                'is_active' => $this->toBoolean($row['is_active'] ?? null, true),
                'show_on_products_page' => $this->toBoolean($row['show_on_products_page'] ?? null, true),
                'show_on_home' => $this->toBoolean($row['show_on_home'] ?? null, false),
                'cover_media_id' => $this->nullableMediaId($row['cover_media_id'] ?? null),
                'banner_media_id' => $this->nullableMediaId($row['banner_media_id'] ?? null),
            ]
        );
    }

    private function upsertSubfamily(array $row, int $rowNumber): ProductSubfamily
    {
        $familySlug = $this->requiredCell($row, 'family_slug', 'subfamilies', $rowNumber);
        $family = ProductFamily::query()->where('slug', Str::slug($familySlug))->first();

        if (! $family) {
            abort(422, "No existe la familia {$familySlug} en subfamilies fila {$rowNumber}.");
        }

        $name = $this->requiredCell($row, 'name', 'subfamilies', $rowNumber);
        $slug = $this->slugValue($row['slug'] ?? null, $name);

        return ProductSubfamily::query()->updateOrCreate(
            [
                'product_family_id' => $family->id,
                'slug' => $slug,
            ],
            [
                'name' => $name,
                'short_description' => $this->nullableString($row['short_description'] ?? null),
                'description' => $this->nullableString($row['description'] ?? null),
                'accent_color' => $this->nullableString($row['accent_color'] ?? null),
                'sort_order' => SortOrder::normalize($row['sort_order'] ?? null, $family->subfamilies()->count() + 1),
                'is_active' => $this->toBoolean($row['is_active'] ?? null, true),
                'show_on_home' => $this->toBoolean($row['show_on_home'] ?? null, false),
                'show_on_family_page' => $this->toBoolean($row['show_on_family_page'] ?? null, true),
            ]
        );
    }

    private function upsertProductCore(array $row, int $rowNumber): array
    {
        $familySlug = $this->requiredCell($row, 'family_slug', 'products', $rowNumber);
        $family = ProductFamily::query()->where('slug', Str::slug($familySlug))->first();

        if (! $family) {
            abort(422, "No existe la familia {$familySlug} en products fila {$rowNumber}.");
        }

        $subfamily = null;
        if (filled($row['subfamily_slug'] ?? null)) {
            $subfamily = ProductSubfamily::query()
                ->where('product_family_id', $family->id)
                ->where('slug', Str::slug((string) $row['subfamily_slug']))
                ->first();

            if (! $subfamily) {
                abort(422, "No existe la subfamilia {$row['subfamily_slug']} en products fila {$rowNumber}.");
            }
        }

        $name = $this->requiredCell($row, 'name', 'products', $rowNumber);
        $slug = $this->slugValue($row['slug'] ?? null, $name);

        $product = Product::query()->updateOrCreate(
            ['slug' => $slug],
            [
                'product_family_id' => $family->id,
                'product_subfamily_id' => $subfamily?->id,
                'name' => $name,
                'sku' => $this->nullableString($row['sku'] ?? null),
                'short_description' => $this->nullableString($row['short_description'] ?? null),
                'description' => $this->nullableString($row['description'] ?? null),
                'material' => $this->nullableString($row['material'] ?? null),
                'treatment' => $this->nullableString($row['treatment'] ?? null),
                'observations' => $this->nullableString($row['observations'] ?? null),
                'main_media_id' => $this->nullableMediaId($row['main_media_id'] ?? null),
                'technical_sheet_media_id' => $this->nullableMediaId($row['technical_sheet_media_id'] ?? null),
                'sort_order' => SortOrder::normalize($row['sort_order'] ?? null, Product::query()->count() + 1),
                'is_active' => $this->toBoolean($row['is_active'] ?? null, true),
                'is_featured_home' => $this->toBoolean($row['is_featured_home'] ?? null, false),
                'is_featured_family' => $this->toBoolean($row['is_featured_family'] ?? null, false),
            ]
        );

        return [
            'product' => $product,
            'row' => $row,
            'row_number' => $rowNumber,
        ];
    }

    private function syncImportedProduct(Product $product, array $row, int $rowNumber): void
    {
        $galleryIds = $this->csvToIntegers($row['gallery_media_ids'] ?? null);
        $product->media()->delete();

        foreach ($galleryIds as $index => $mediaId) {
            if (! MediaAsset::query()->whereKey($mediaId)->exists()) {
                continue;
            }

            ProductMedia::query()->create([
                'product_id' => $product->id,
                'media_id' => $mediaId,
                'sort_order' => SortOrder::fromPosition($index + 1),
                'is_primary' => false,
            ]);
        }

        $specTables = $this->parseSpecTables($row['spec_tables_json'] ?? null, $rowNumber);
        $product->specTables()->delete();

        foreach ($specTables as $tableIndex => $tableData) {
            $table = ProductSpecTable::query()->create([
                'product_id' => $product->id,
                'title' => $this->nullableString($tableData['title'] ?? null),
                'sort_order' => SortOrder::fromPosition($tableIndex + 1),
            ]);

            $columnIds = [];
            foreach (($tableData['columns'] ?? []) as $columnIndex => $columnData) {
                if (! filled($columnData['label'] ?? null)) {
                    continue;
                }

                $column = ProductSpecColumn::query()->create([
                    'product_spec_table_id' => $table->id,
                    'label' => $columnData['label'],
                    'unit' => $this->nullableString($columnData['unit'] ?? null),
                    'sort_order' => SortOrder::fromPosition($columnIndex + 1),
                ]);

                $columnIds[] = $column->id;
            }

            foreach (($tableData['rows'] ?? []) as $rowIndex => $rowValues) {
                $specRow = ProductSpecRow::query()->create([
                    'product_spec_table_id' => $table->id,
                    'sort_order' => SortOrder::fromPosition($rowIndex + 1),
                ]);

                foreach ((array) $rowValues as $valueIndex => $value) {
                    if (! isset($columnIds[$valueIndex])) {
                        continue;
                    }

                    ProductSpecValue::query()->create([
                        'product_spec_row_id' => $specRow->id,
                        'product_spec_column_id' => $columnIds[$valueIndex],
                        'value' => $this->nullableString($value),
                    ]);
                }
            }
        }

        ProductRelated::query()->where('product_id', $product->id)->delete();
        foreach ($this->csvToStrings($row['related_product_slugs'] ?? null) as $index => $slug) {
            $related = Product::query()->where('slug', Str::slug($slug))->first();

            if (! $related || $related->id === $product->id) {
                continue;
            }

            ProductRelated::query()->create([
                'product_id' => $product->id,
                'related_product_id' => $related->id,
                'sort_order' => SortOrder::fromPosition($index + 1),
            ]);
        }
    }

    private function parseSpecTables(mixed $value, int $rowNumber): array
    {
        $value = trim((string) $value);

        if ($value === '') {
            return [];
        }

        $decoded = json_decode($value, true);

        if (! is_array($decoded)) {
            abort(422, "El JSON de spec_tables_json es inválido en products fila {$rowNumber}.");
        }

        return $decoded;
    }

    private function requiredCell(array $row, string $key, string $sheet, int $rowNumber): string
    {
        $value = trim((string) ($row[$key] ?? ''));

        if ($value === '') {
            abort(422, "Falta {$key} en {$sheet} fila {$rowNumber}.");
        }

        return $value;
    }

    private function slugValue(mixed $slug, string $fallback): string
    {
        $value = trim((string) $slug);

        return Str::slug($value !== '' ? $value : $fallback);
    }

    private function nullableString(mixed $value): ?string
    {
        $string = trim((string) $value);

        return $string === '' ? null : $string;
    }

    private function nullableMediaId(mixed $value): ?int
    {
        $string = trim((string) $value);

        if ($string === '') {
            return null;
        }

        $id = (int) $string;

        return MediaAsset::query()->whereKey($id)->exists() ? $id : null;
    }

    private function toBoolean(mixed $value, bool $default = false): bool
    {
        $normalized = Str::lower(trim((string) $value));

        if ($normalized === '') {
            return $default;
        }

        return in_array($normalized, ['1', 'true', 'si', 'sí', 'yes', 'y', 'x'], true);
    }

    private function csvToIntegers(mixed $value): array
    {
        return collect(explode(',', (string) $value))
            ->map(fn ($item) => (int) trim($item))
            ->filter()
            ->values()
            ->all();
    }

    private function csvToStrings(mixed $value): array
    {
        return collect(explode(',', (string) $value))
            ->map(fn ($item) => trim($item))
            ->filter()
            ->values()
            ->all();
    }
}
