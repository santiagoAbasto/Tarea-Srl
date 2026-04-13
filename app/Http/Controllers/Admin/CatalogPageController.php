<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\CatalogRequest;
use App\Models\SectionFieldValue;
use App\Models\SiteSection;
use Inertia\Inertia;
use Inertia\Response;

class CatalogPageController extends Controller
{
    public function index(): Response
    {
        $hero = $this->section(
            [
                'page_key' => 'catalogo',
                'section_key' => 'catalogo_banner',
            ],
            [
                'title' => 'CATÁLOGO',
                'sort_order' => 'A',
                'is_active' => true,
            ]
        )->load('media');

        $content = $this->section(
            [
                'page_key' => 'catalogo',
                'section_key' => 'catalogo_content',
            ],
            [
                'title' => 'Solicita nuestro catálogo completo',
                'sort_order' => 'B',
                'is_active' => true,
            ]
        );

        $this->ensureField($content, 'name_label', 'Nombre y Apellido*', 'A');
        $this->ensureField($content, 'email_label', 'Email*', 'B');
        $this->ensureField($content, 'company_label', 'Empresa*', 'C');
        $this->ensureField($content, 'phone_label', 'Teléfono*', 'D');

        $content->load('fieldValues');

        return Inertia::render('Admin/Catalog/Index', [
            'hero' => [
                'id' => $hero->id,
                'title' => $hero->title,
                'media_id' => $hero->media_id,
                'media_url' => media_asset_url($hero->media),
                'sort_order' => $hero->sort_order,
                'is_active' => $hero->is_active,
            ],
            'content' => [
                'id' => $content->id,
                'title' => $content->title,
                'description' => $content->description,
                'sort_order' => $content->sort_order,
                'is_active' => $content->is_active,
                'fields' => $content->fieldValues->map(fn (SectionFieldValue $field) => [
                    'id' => $field->id,
                    'field_key' => $field->field_key,
                    'field_label' => $field->field_label,
                    'field_type' => $field->field_type,
                    'field_value' => $field->field_value,
                    'sort_order' => $field->sort_order,
                    'is_active' => $field->is_active,
                ])->values(),
            ],
            'requests' => CatalogRequest::query()
                ->orderByDesc('created_at')
                ->get()
                ->map(fn (CatalogRequest $request) => [
                    'id' => $request->id,
                    'name' => $request->name,
                    'email' => $request->email,
                    'company' => $request->company,
                    'phone' => $request->phone,
                    'message' => $request->message,
                    'is_read' => $request->is_read,
                    'status' => $request->status,
                    'reply_url' => $request->reply_url,
                    'created_at' => optional($request->created_at)?->format('d/m/Y H:i'),
                ])
                ->values(),
            'stats' => [
                'requests' => CatalogRequest::query()->count(),
                'unread' => CatalogRequest::query()->where('is_read', false)->count(),
            ],
            'initialTab' => request()->string('tab')->toString() === 'requests'
                ? 'requests'
                : 'page',
            'publicCatalogUrl' => route('web.catalog.show'),
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
