<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\QuoteRequest;
use App\Models\SectionFieldValue;
use App\Models\SiteSection;
use Inertia\Inertia;
use Inertia\Response;

class QuotePageController extends Controller
{
    public function index(): Response
    {
        $hero = SiteSection::query()->firstOrCreate(
            [
                'page_key' => 'presupuesto',
                'section_key' => 'presupuesto_banner',
            ],
            [
                'title' => 'SOLICITUD DE PRESUPUESTO',
                'sort_order' => 'A',
                'is_active' => true,
            ],
        )->load('media');

        $content = SiteSection::query()->firstOrCreate(
            [
                'page_key' => 'presupuesto',
                'section_key' => 'presupuesto_content',
            ],
            [
                'title' => 'Datos personales',
                'subtitle' => 'Productos seleccionados',
                'sort_order' => 'B',
                'is_active' => true,
            ],
        );

        $this->ensureField($content, 'name_label', 'Nombre y Apellido*', 'A');
        $this->ensureField($content, 'email_label', 'Email*', 'B');
        $this->ensureField($content, 'company_label', 'Empresa', 'C');
        $this->ensureField($content, 'phone_label', 'Teléfono*', 'D');
        $this->ensureField($content, 'message_label', 'Mensaje', 'E');
        $this->ensureField($content, 'button_text', 'Enviar solicitud', 'F');

        $content->load('fieldValues');

        return Inertia::render('Admin/Quote/Index', [
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
                'subtitle' => $content->subtitle,
                'sort_order' => $content->sort_order,
                'is_active' => $content->is_active,
                'fields' => $content->fieldValues->map(fn (SectionFieldValue $field): array => [
                    'id' => $field->id,
                    'field_key' => $field->field_key,
                    'field_label' => $field->field_label,
                    'field_type' => $field->field_type,
                    'field_value' => $field->field_value,
                    'sort_order' => $field->sort_order,
                    'is_active' => $field->is_active,
                ])->values(),
            ],
            'requests' => QuoteRequest::query()
                ->with(['items', 'attachments.media'])
                ->orderByDesc('created_at')
                ->get()
                ->map(fn (QuoteRequest $request): array => [
                    'id' => $request->id,
                    'name' => $request->name,
                    'email' => $request->email,
                    'company' => $request->company,
                    'phone' => $request->phone,
                    'message' => $request->message,
                    'is_read' => $request->is_read,
                    'status' => $request->status,
                    'created_at' => optional($request->created_at)?->format('d/m/Y H:i'),
                    'reply_url' => $request->email
                        ? 'mailto:'.$request->email.'?subject='.rawurlencode('Re: Solicitud de presupuesto - Tarea SRL')
                        : null,
                    'items' => $request->items->map(fn ($item): array => [
                        'product_name' => $item->product_name_snapshot ?: ($item->product?->name ?? 'Producto'),
                        'quantity' => $item->quantity,
                    ])->values(),
                    'attachments' => $request->attachments->map(fn ($attachment): array => [
                        'id' => $attachment->id,
                        'name' => $attachment->file_name_snapshot ?: ($attachment->media?->title ?? 'Adjunto'),
                        'url' => media_asset_url($attachment->media),
                    ])->values(),
                ])
                ->values(),
            'stats' => [
                'requests' => QuoteRequest::query()->count(),
                'unread' => QuoteRequest::query()->where('is_read', false)->count(),
            ],
            'initialTab' => request()->string('tab')->toString() === 'requests'
                ? 'requests'
                : 'page',
            'publicQuoteUrl' => route('web.quote.show'),
        ]);
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
