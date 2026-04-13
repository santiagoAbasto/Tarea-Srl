<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\SectionItem;
use App\Models\SiteSection;
use Inertia\Inertia;
use Inertia\Response;

class AboutPageController extends Controller
{
    public function index(): Response
    {
        $hero = $this->ensureSection(
            ['page_key' => 'nosotros', 'section_key' => 'hero'],
            ['title' => 'NOSOTROS', 'sort_order' => 'A', 'is_active' => true]
        );

        $intro = $this->ensureSection(
            ['page_key' => 'nosotros', 'section_key' => 'intro'],
            ['title' => 'Quienes somos', 'description' => '', 'sort_order' => 'AA', 'is_active' => true]
        );

        $missionVision = $this->ensureSection(
            ['page_key' => 'nosotros', 'section_key' => 'mission_vision'],
            ['sort_order' => 'AB', 'is_active' => true]
        );

        $values = $this->ensureSection(
            ['page_key' => 'nosotros', 'section_key' => 'values'],
            ['title' => 'Nuestros valores', 'sort_order' => 'AC', 'is_active' => true]
        );

        $memberships = $this->ensureSection(
            ['page_key' => 'nosotros', 'section_key' => 'memberships'],
            ['title' => 'Pertenecemos a', 'sort_order' => 'AD', 'is_active' => true]
        );

        $this->ensureSectionItem($missionVision, 'mission', [
            'title' => 'Misión',
            'description' => '',
            'sort_order' => 'A',
            'is_active' => true,
        ]);

        $this->ensureSectionItem($missionVision, 'vision', [
            'title' => 'Visión',
            'description' => '',
            'sort_order' => 'AA',
            'is_active' => true,
        ]);

        foreach ([
            ['item_key' => 'entrepreneurial-spirit', 'title' => "Espíritu\nEmprendedor", 'sort_order' => 'A'],
            ['item_key' => 'flexibility', 'title' => 'Flexibilidad', 'sort_order' => 'AA'],
            ['item_key' => 'commitment', 'title' => 'Compromiso', 'sort_order' => 'AB'],
            ['item_key' => 'teamwork', 'title' => "Trabajo en\nequipo", 'sort_order' => 'AC'],
            ['item_key' => 'human-value', 'title' => "Valor\nHumano", 'sort_order' => 'AD'],
        ] as $item) {
            $this->ensureSectionItem($values, $item['item_key'], $item + ['is_active' => true]);
        }

        if ($memberships->items()->count() === 0) {
            foreach ([
                ['item_key' => 'cadieel', 'title' => 'CADIEEL', 'sort_order' => 'A'],
                ['item_key' => 'gapp', 'title' => 'GAPP', 'sort_order' => 'AA'],
            ] as $item) {
                $this->ensureSectionItem($memberships, $item['item_key'], $item + ['is_active' => true]);
            }
        }

        return Inertia::render('Admin/About/Index', [
            'sections' => [
                'hero' => $this->serializeSection($hero->fresh(['media'])),
                'intro' => $this->serializeSection($intro->fresh(['media'])),
                'missionVision' => $this->serializeSection($missionVision->fresh(['items.media'])),
                'values' => $this->serializeSection($values->fresh(['items.media'])),
                'memberships' => $this->serializeSection($memberships->fresh(['items.media'])),
            ],
            'publicAboutUrl' => route('web.about'),
        ]);
    }

    private function ensureSection(array $attributes, array $defaults): SiteSection
    {
        return SiteSection::query()->firstOrCreate($attributes, $defaults);
    }

    private function ensureSectionItem(SiteSection $section, string $itemKey, array $defaults): SectionItem
    {
        return SectionItem::query()->firstOrCreate(
            [
                'site_section_id' => $section->id,
                'item_key' => $itemKey,
            ],
            $defaults
        );
    }

    private function serializeSection(?SiteSection $section): ?array
    {
        if (! $section) {
            return null;
        }

        return [
            'id' => $section->id,
            'page_key' => $section->page_key,
            'section_key' => $section->section_key,
            'title' => $section->title,
            'subtitle' => $section->subtitle,
            'description' => $section->description,
            'button_text' => $section->button_text,
            'button_url' => $section->button_url,
            'media_id' => $section->media_id,
            'media_url' => media_asset_url($section->media),
            'is_active' => $section->is_active,
            'sort_order' => $section->sort_order,
            'items' => $section->items
                ->map(fn (SectionItem $item) => [
                    'id' => $item->id,
                    'item_key' => $item->item_key,
                    'title' => $item->title,
                    'subtitle' => $item->subtitle,
                    'description' => $item->description,
                    'media_id' => $item->media_id,
                    'media_url' => media_asset_url($item->media),
                    'link_url' => $item->link_url,
                    'accent_color' => $item->accent_color,
                    'is_active' => $item->is_active,
                    'sort_order' => $item->sort_order,
                ])
                ->values()
                ->all(),
        ];
    }
}
