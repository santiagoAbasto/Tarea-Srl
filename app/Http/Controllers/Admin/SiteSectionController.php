<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\SectionFieldValue;
use App\Models\SectionItem;
use App\Models\SiteSection;
use App\Support\CmsSecurity;
use App\Support\SortOrder;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class SiteSectionController extends Controller
{
    public function index(Request $request): JsonResponse
    {
        $sections = SiteSection::query()
            ->with(['media', 'secondaryMedia', 'fieldValues', 'items.media'])
            ->when($request->filled('page_key'), fn ($query) => $query->where('page_key', $request->string('page_key')))
            ->orderBy('page_key')
            ->orderBy('sort_order')
            ->get();

        return response()->json($sections);
    }

    public function show(int|string $id): JsonResponse
    {
        return response()->json(
            SiteSection::query()->with(['media', 'secondaryMedia', 'fieldValues', 'items.media'])->findOrFail($id)
        );
    }

    public function store(Request $request): JsonResponse
    {
        $validated = $this->validatePayload($request);

        $section = DB::transaction(function () use ($validated) {
            $section = SiteSection::create($this->sectionPayload($validated));
            $this->syncFieldValues($section, $validated['field_values'] ?? []);
            $this->syncItems($section, $validated['items'] ?? []);

            return $section;
        });

        return response()->json($section->load(['fieldValues', 'items']), 201);
    }

    public function update(Request $request, int|string $id): JsonResponse
    {
        $section = SiteSection::query()->findOrFail($id);
        $validated = $this->validatePayload($request, $section->id);

        DB::transaction(function () use ($section, $validated) {
            $section->fill($this->sectionPayload($validated, $section))->save();
            $this->syncFieldValues($section, $validated['field_values'] ?? []);
            $this->syncItems($section, $validated['items'] ?? []);
        });

        return response()->json($section->fresh(['fieldValues', 'items.media']));
    }

    public function destroy(int|string $id): JsonResponse
    {
        SiteSection::query()->findOrFail($id)->delete();

        return response()->json(['deleted' => true]);
    }

    protected function validatePayload(Request $request, ?int $sectionId = null): array
    {
        return $request->validate([
            'page_key' => ['required', 'string', 'max:100'],
            'section_key' => ['required', 'string', 'max:100'],
            'title' => ['nullable', 'string', 'max:255'],
            'subtitle' => ['nullable', 'string', 'max:255'],
            'description' => ['nullable', 'string'],
            'media_id' => ['nullable', 'integer', 'exists:media_assets,id'],
            'secondary_media_id' => ['nullable', 'integer', 'exists:media_assets,id'],
            'button_text' => ['nullable', 'string', 'max:255'],
            'button_url' => ['nullable', 'string', 'max:255'],
            'meta_json' => ['nullable', 'array'],
            'sort_order' => ['nullable'],
            'is_active' => ['nullable', 'boolean'],
            'field_values' => ['nullable', 'array'],
            'field_values.*.id' => ['nullable', 'integer'],
            'field_values.*.field_key' => ['required', 'string', 'max:100'],
            'field_values.*.field_label' => ['nullable', 'string', 'max:255'],
            'field_values.*.field_type' => ['nullable', 'string', 'max:50'],
            'field_values.*.field_value' => ['nullable', 'string'],
            'field_values.*.sort_order' => ['nullable'],
            'field_values.*.is_active' => ['nullable', 'boolean'],
            'items' => ['nullable', 'array'],
            'items.*.id' => ['nullable', 'integer'],
            'items.*.item_key' => ['nullable', 'string', 'max:100'],
            'items.*.title' => ['nullable', 'string', 'max:255'],
            'items.*.subtitle' => ['nullable', 'string', 'max:255'],
            'items.*.description' => ['nullable', 'string'],
            'items.*.media_id' => ['nullable', 'integer', 'exists:media_assets,id'],
            'items.*.link_url' => ['nullable', 'string', 'max:255'],
            'items.*.accent_color' => ['nullable', 'string', 'max:50'],
            'items.*.meta_json' => ['nullable', 'array'],
            'items.*.sort_order' => ['nullable'],
            'items.*.is_active' => ['nullable', 'boolean'],
        ]);
    }

    protected function sectionPayload(array $validated, ?SiteSection $section = null): array
    {
        if (array_key_exists('sort_order', $validated) || $section === null) {
            $validated['sort_order'] = SortOrder::normalize(
                $validated['sort_order'] ?? null,
                SiteSection::query()
                    ->where('page_key', $validated['page_key'] ?? $section?->page_key)
                    ->count() + 1
            );
        }

        return collect($validated)->only([
            'page_key',
            'section_key',
            'title',
            'subtitle',
            'description',
            'media_id',
            'secondary_media_id',
            'button_text',
            'button_url',
            'meta_json',
            'sort_order',
            'is_active',
        ])->mapWithKeys(function ($value, string $key): array {
            return [$key => match ($key) {
                'page_key', 'section_key', 'title', 'subtitle', 'button_text' => cms_plain_text(is_string($value) ? $value : null),
                'description' => cms_rich_text(is_string($value) ? $value : null),
                'button_url' => CmsSecurity::sanitizeUrl(is_string($value) ? $value : null, true),
                'meta_json' => cms_sanitize_array($value),
                default => $value,
            }];
        })->all();
    }

    protected function syncFieldValues(SiteSection $section, array $rows): void
    {
        $keptIds = [];

        foreach (array_values($rows) as $index => $row) {
            $field = SectionFieldValue::query()->updateOrCreate(
                [
                    'id' => $row['id'] ?? null,
                    'site_section_id' => $section->id,
                ],
                [
                    'field_key' => cms_plain_text($row['field_key']) ?? '',
                    'field_label' => cms_plain_text($row['field_label'] ?? null),
                    'field_type' => cms_plain_text($row['field_type'] ?? 'text') ?? 'text',
                    'field_value' => cms_rich_text($row['field_value'] ?? null),
                    'sort_order' => SortOrder::normalize($row['sort_order'] ?? null, $index + 1),
                    'is_active' => $row['is_active'] ?? true,
                ]
            );

            $keptIds[] = $field->id;
        }

        $section->fieldValues()->whereNotIn('id', $keptIds)->delete();
    }

    protected function syncItems(SiteSection $section, array $rows): void
    {
        $keptIds = [];

        foreach (array_values($rows) as $index => $row) {
            $item = SectionItem::query()->updateOrCreate(
                [
                    'id' => $row['id'] ?? null,
                    'site_section_id' => $section->id,
                ],
                [
                    'item_key' => cms_plain_text($row['item_key'] ?? null),
                    'title' => cms_plain_text($row['title'] ?? null),
                    'subtitle' => cms_plain_text($row['subtitle'] ?? null),
                    'description' => cms_rich_text($row['description'] ?? null),
                    'media_id' => $row['media_id'] ?? null,
                    'link_url' => CmsSecurity::sanitizeUrl($row['link_url'] ?? null, true),
                    'accent_color' => cms_plain_text($row['accent_color'] ?? null),
                    'meta_json' => cms_sanitize_array($row['meta_json'] ?? null),
                    'sort_order' => SortOrder::normalize($row['sort_order'] ?? null, $index + 1),
                    'is_active' => $row['is_active'] ?? true,
                ]
            );

            $keptIds[] = $item->id;
        }

        $section->items()->whereNotIn('id', $keptIds)->delete();
    }
}
