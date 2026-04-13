<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\SectionFieldValue;
use App\Models\SectionItem;
use App\Models\SiteSection;
use Inertia\Inertia;
use Inertia\Response;

class QualityPageController extends Controller
{
    public function index(): Response
    {
        $hero = $this->section(
            [
                'page_key' => 'calidad',
                'section_key' => 'quality_banner',
            ],
            [
                'title' => 'CALIDAD',
                'sort_order' => 'A',
                'is_active' => true,
            ]
        )->load('media');

        $policy = $this->section(
            [
                'page_key' => 'calidad',
                'section_key' => 'quality_policy',
            ],
            [
                'title' => 'Políticas de calidad',
                'sort_order' => 'B',
                'is_active' => true,
            ]
        );

        $this->ensureField($policy, 'media_type', 'image', 'A');
        $this->ensureField($policy, 'youtube_url', '', 'B');

        $policy->load(['media', 'secondaryMedia', 'fieldValues']);

        $logos = $this->section(
            [
                'page_key' => 'calidad',
                'section_key' => 'quality_logos',
            ],
            [
                'title' => 'Logos de calidad',
                'sort_order' => 'C',
                'is_active' => true,
            ]
        )->load(['items.media']);

        return Inertia::render('Admin/Quality/Index', [
            'hero' => [
                'id' => $hero->id,
                'title' => $hero->title,
                'media_id' => $hero->media_id,
                'media_url' => media_asset_url($hero->media),
                'sort_order' => $hero->sort_order,
                'is_active' => $hero->is_active,
            ],
            'policy' => [
                'id' => $policy->id,
                'title' => $policy->title,
                'description' => $policy->description,
                'media_id' => $policy->media_id,
                'media_url' => media_asset_url($policy->media),
                'secondary_media_id' => $policy->secondary_media_id,
                'secondary_media_url' => media_asset_url($policy->secondaryMedia),
                'sort_order' => $policy->sort_order,
                'is_active' => $policy->is_active,
                'fields' => $policy->fieldValues->map(fn (SectionFieldValue $field): array => [
                    'id' => $field->id,
                    'field_key' => $field->field_key,
                    'field_label' => $field->field_label,
                    'field_type' => $field->field_type,
                    'field_value' => $field->field_value,
                    'sort_order' => $field->sort_order,
                    'is_active' => $field->is_active,
                ])->values(),
            ],
            'logos' => [
                'id' => $logos->id,
                'title' => $logos->title,
                'sort_order' => $logos->sort_order,
                'is_active' => $logos->is_active,
                'items' => $logos->items->map(fn (SectionItem $item): array => [
                    'id' => $item->id,
                    'item_key' => $item->item_key,
                    'title' => $item->title,
                    'media_id' => $item->media_id,
                    'media_url' => media_asset_url($item->media),
                    'sort_order' => $item->sort_order,
                    'is_active' => $item->is_active,
                ])->values(),
            ],
            'stats' => [
                'logos' => $logos->items()->where('is_active', true)->count(),
            ],
            'publicQualityUrl' => route('web.quality'),
        ]);
    }

    protected function section(array $attributes, array $defaults): SiteSection
    {
        return SiteSection::query()->firstOrCreate($attributes, $defaults);
    }

    protected function ensureField(SiteSection $section, string $key, string $value, string $sortOrder): void
    {
        SectionFieldValue::query()->firstOrCreate(
            [
                'site_section_id' => $section->id,
                'field_key' => $key,
            ],
            [
                'field_label' => $key,
                'field_type' => 'text',
                'field_value' => $value,
                'sort_order' => $sortOrder,
                'is_active' => true,
            ]
        );
    }
}
