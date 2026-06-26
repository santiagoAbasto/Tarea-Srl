<?php

namespace App\Http\Controllers\Web;

use App\Http\Controllers\Controller;
use App\Mail\QuoteRequestNotification;
use App\Models\FooterSetting;
use App\Models\MediaAsset;
use App\Models\Product;
use App\Models\QuoteRequest;
use App\Models\QuoteRequestAttachment;
use App\Models\QuoteRequestItem;
use App\Models\SiteSection;
use App\Models\SocialLink;
use App\Support\CmsSecurity;
use App\Support\SortOrder;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Mail;
use Illuminate\View\View;

class QuoteController extends Controller
{
    protected const SELECTION_TTL_SECONDS = 1800;
    protected const MAX_SELECTED_PRODUCTS = 5;

    public function show(Request $request): View
    {
        if ($request->boolean('clear_selection')) {
            $request->session()->forget('quote.selected_products');
            $request->session()->forget('quote.selected_products_touched_at');
        }

        $sections = SiteSection::query()->with(['media', 'fieldValues'])
                ->where('page_key', 'presupuesto')
                ->where('is_active', true)
                ->orderBy('sort_order')
                ->get()
                ->keyBy('section_key');

        $headerLogo = MediaAsset::query()->where('path', 'uploads/branding/logoheader.png')->first();

        $selectedProductIds = $this->resolveSelectedProductIds($request);

        $selectedProducts = Product::query()
            ->with(['mainMedia', 'family', 'subfamily'])
            ->whereIn('id', $selectedProductIds)
            ->where('is_active', true)
            ->get()
            ->sortBy(fn (Product $product) => $selectedProductIds->search($product->id))
            ->take(self::MAX_SELECTED_PRODUCTS)
            ->values();

        $content = $sections->get('presupuesto_content');
        $fields = $content?->fieldValues?->keyBy('field_key');

        return view('web.quote.show', [
            'sections' => $sections,
            'hero' => $sections->get('presupuesto_banner'),
            'content' => $content,
            'fields' => $fields,
            'selectedProducts' => $selectedProducts,
            'selectionTtlSeconds' => self::SELECTION_TTL_SECONDS,
            'headerLogo' => $headerLogo,
            'headerLogoUrl' => $headerLogo ? media_asset_url($headerLogo) : null,
            'footer' => FooterSetting::query()->with('logo')->first(),
            'social_links' => SocialLink::query()->where('location', 'footer')->where('is_active', true)->orderBy('sort_order')->get(),
        ]);
    }

    public function store(Request $request)
    {
        $allowedAttachmentExtensions = implode(',', CmsSecurity::publicAttachmentExtensions());
        $allowedAttachmentMimeTypes = implode(',', CmsSecurity::publicAttachmentMimeTypes());

        $validated = $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'email' => ['required', 'email'],
            'company' => ['nullable', 'string', 'max:255'],
            'phone' => ['nullable', 'string', 'max:255'],
            'message' => ['nullable', 'string'],
            'items' => ['nullable', 'array'],
            'items.*.product_id' => ['nullable', 'integer', 'exists:products,id'],
            'items.*.product_name' => ['nullable', 'string', 'max:255'],
            'items.*.quantity' => ['required', 'integer', 'min:1'],
            'attachments' => ['nullable', 'array'],
            'attachments.*' => ['file', 'mimes:'.$allowedAttachmentExtensions, 'mimetypes:'.$allowedAttachmentMimeTypes, 'max:51200'],
        ]);

        $quote = DB::transaction(function () use ($request, $validated) {
            $quote = QuoteRequest::create([
                'name' => cms_plain_text($validated['name']) ?? '',
                'email' => cms_email($validated['email']) ?? '',
                'company' => cms_plain_text($validated['company'] ?? null),
                'phone' => cms_phone($validated['phone'] ?? null),
                'message' => cms_plain_text($validated['message'] ?? null),
            ]);

            foreach (($validated['items'] ?? []) as $item) {
                $product = ! empty($item['product_id']) ? Product::query()->find($item['product_id']) : null;

                QuoteRequestItem::create([
                    'quote_request_id' => $quote->id,
                    'product_id' => $product?->id,
                    'product_name_snapshot' => cms_plain_text($item['product_name'] ?? $product?->name ?? 'Producto') ?? 'Producto',
                    'product_sku_snapshot' => $product?->sku,
                    'quantity' => $item['quantity'],
                ]);
            }

            foreach ($request->file('attachments', []) as $index => $file) {
                $path = $file->store('quote-attachments/'.now()->format('Y/m'), 'public');
                $mimeType = $file->getMimeType() ?: 'application/octet-stream';

                $media = \App\Models\MediaAsset::create([
                    'type' => CmsSecurity::mediaAssetTypeForMime($mimeType),
                    'disk' => 'public',
                    'path' => $path,
                    'title' => pathinfo($file->getClientOriginalName(), PATHINFO_FILENAME),
                    'mime_type' => $mimeType,
                    'extension' => $file->getClientOriginalExtension(),
                    'size_bytes' => $file->getSize(),
                ]);

                QuoteRequestAttachment::create([
                    'quote_request_id' => $quote->id,
                    'media_id' => $media->id,
                    'file_name_snapshot' => cms_plain_text($file->getClientOriginalName()) ?? 'adjunto',
                    'sort_order' => SortOrder::fromPosition($index + 1),
                ]);
            }

            return $quote;
        });

        cms_security_log('info', 'Public quote form submitted.', [
            'quote_request_id' => $quote->id,
        ], $request);

        $quote->load(['items.product.mainMedia', 'attachments.media']);
        $this->sendQuoteNotification($quote);

        if ($request->expectsJson()) {
            return response()->json($quote->load(['items.product.mainMedia', 'attachments.media']), 201);
        }

        $selectedIds = collect($validated['items'] ?? [])->pluck('product_id')->filter()->values();

        if ($selectedIds->isNotEmpty()) {
            $request->session()->put('quote.selected_products', $selectedIds->take(self::MAX_SELECTED_PRODUCTS)->all());
            $request->session()->put('quote.selected_products_touched_at', now()->timestamp);
        }

        $request->session()->forget('quote.selected_products');
        $request->session()->forget('quote.selected_products_touched_at');

        return redirect()
            ->route('web.quote.show')
            ->with('quote_success', 'Tu solicitud fue enviada correctamente.');
    }

    protected function resolveSelectedProductIds(Request $request)
    {
        $this->clearExpiredSelection($request);

        $incomingIds = collect(
            array_filter(
                array_map(
                    'intval',
                    array_merge(
                        $request->array('products'),
                        $request->filled('products') ? explode(',', (string) $request->string('products')) : [],
                        $request->filled('product') ? [(string) $request->string('product')] : [],
                        $request->filled('product_id') ? [(string) $request->string('product_id')] : []
                    )
                )
            )
        )->filter(fn ($id) => $id > 0)->values();

        $storedIds = collect($request->session()->get('quote.selected_products', []))
            ->map(fn ($id) => (int) $id)
            ->filter(fn ($id) => $id > 0)
            ->take(self::MAX_SELECTED_PRODUCTS)
            ->values();

        $selectedIds = $storedIds;

        if ($incomingIds->isNotEmpty()) {
            $selectedIds = $storedIds
                ->merge($incomingIds)
                ->unique()
                ->take(self::MAX_SELECTED_PRODUCTS)
                ->values();

            $request->session()->put('quote.selected_products', $selectedIds->all());
            $request->session()->put('quote.selected_products_touched_at', now()->timestamp);
        }

        if ($selectedIds->isNotEmpty()) {
            $request->session()->put('quote.selected_products_touched_at', now()->timestamp);
        }

        return $selectedIds;
    }

    protected function clearExpiredSelection(Request $request): void
    {
        $touchedAt = (int) $request->session()->get('quote.selected_products_touched_at', 0);

        if ($touchedAt <= 0) {
            return;
        }

        if (now()->diffInSeconds(\Carbon\Carbon::createFromTimestamp($touchedAt)) < self::SELECTION_TTL_SECONDS) {
            return;
        }

        $request->session()->forget('quote.selected_products');
        $request->session()->forget('quote.selected_products_touched_at');
    }

    protected function sendQuoteNotification(QuoteRequest $quote): void
    {
        $to = $this->resolveQuoteNotificationRecipients();

        if ($to->isEmpty()) {
            Log::warning('Quote request notification skipped because no recipient email is configured.', [
                'quote_request_id' => $quote->id,
            ]);

            return;
        }

        $adminUrl = cms_admin_login_url();

        try {
            Mail::to($to->all())->send(new QuoteRequestNotification($quote, $adminUrl));
        } catch (\Throwable $exception) {
            Log::error('Quote request notification failed to send.', [
                'quote_request_id' => $quote->id,
                'recipients' => $to->all(),
                'message' => $exception->getMessage(),
            ]);
        }
    }

    protected function resolveQuoteNotificationRecipients()
    {
        return collect([
            cms_email(config('mail.quote_request.to')),
            cms_email(config('mail.quote_request.cc')),
        ])->filter()->unique()->values();
    }
}
