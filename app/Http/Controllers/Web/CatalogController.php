<?php

namespace App\Http\Controllers\Web;

use App\Http\Controllers\Controller;
use App\Mail\CatalogRequestNotification;
use App\Models\CatalogRequest;
use App\Models\FooterSetting;
use App\Models\MediaAsset;
use App\Models\SiteSection;
use App\Models\SocialLink;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Mail;
use Illuminate\View\View;

class CatalogController extends Controller
{
    public function show(): View
    {
        $sections = SiteSection::query()
            ->with(['media', 'fieldValues'])
            ->where('page_key', 'catalogo')
            ->where('is_active', true)
            ->orderBy('sort_order')
            ->get()
            ->keyBy('section_key');

        $headerLogo = MediaAsset::query()->where('path', 'uploads/branding/logoheader.png')->first();
        $content = $sections->get('catalogo_content');
        $fields = $content?->fieldValues?->keyBy('field_key');

        return view('web.catalog.show', [
            'sections' => $sections,
            'hero' => $sections->get('catalogo_banner'),
            'content' => $content,
            'fields' => $fields,
            'headerLogo' => $headerLogo,
            'headerLogoUrl' => $headerLogo ? media_asset_url($headerLogo) : null,
            'footer' => FooterSetting::query()->with('logo')->first(),
            'social_links' => SocialLink::query()->where('location', 'footer')->where('is_active', true)->orderBy('sort_order')->get(),
        ]);
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'email' => ['required', 'email'],
            'company' => ['nullable', 'string', 'max:255'],
            'phone' => ['nullable', 'string', 'max:255'],
            'message' => ['nullable', 'string'],
        ]);

        $catalogRequest = CatalogRequest::create([
            'name' => cms_plain_text($validated['name']) ?? '',
            'email' => cms_email($validated['email']) ?? '',
            'company' => cms_plain_text($validated['company'] ?? null),
            'phone' => cms_phone($validated['phone'] ?? null),
            'message' => cms_plain_text($validated['message'] ?? null),
        ]);

        cms_security_log('info', 'Public catalog form submitted.', [
            'catalog_request_id' => $catalogRequest->id,
        ], $request);

        $this->sendCatalogNotification($catalogRequest);

        if ($request->expectsJson()) {
            return response()->json($catalogRequest, 201);
        }

        return redirect()
            ->route('web.catalog.show')
            ->with('catalog_success', 'Tu solicitud fue enviada correctamente.');
    }

    protected function sendCatalogNotification(CatalogRequest $catalogRequest): void
    {
        $to = $this->resolveCatalogNotificationRecipients();

        if ($to->isEmpty()) {
            Log::warning('Catalog request notification skipped because no recipient email is configured.', [
                'catalog_request_id' => $catalogRequest->id,
            ]);

            return;
        }

        $adminUrl = cms_admin_login_url();

        try {
            Mail::to($to->all())->send(new CatalogRequestNotification($catalogRequest, $adminUrl));

            Log::info('Catalog request notification sent successfully.', [
                'catalog_request_id' => $catalogRequest->id,
                'recipients' => $to->all(),
            ]);
        } catch (\Throwable $exception) {
            Log::error('Catalog request notification failed to send.', [
                'catalog_request_id' => $catalogRequest->id,
                'recipients' => $to->all(),
                'message' => $exception->getMessage(),
            ]);
        }
    }

    protected function resolveCatalogNotificationRecipients()
    {
        $footer = FooterSetting::query()->first();

        return collect([
            cms_email(config('mail.catalog_request.to')),
            cms_email(config('mail.catalog_request.cc')),
            cms_email(config('mail.quote_request.to')),
            cms_email(config('mail.quote_request.cc')),
            cms_email($footer?->email_primary),
            cms_email($footer?->email_secondary),
        ])->filter()->unique()->values();
    }
}
