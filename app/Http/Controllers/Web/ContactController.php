<?php

namespace App\Http\Controllers\Web;

use App\Http\Controllers\Controller;
use App\Mail\ContactRequestNotification;
use App\Models\ContactPageSetting;
use App\Models\ContactRequest;
use App\Models\FooterSetting;
use App\Models\MediaAsset;
use App\Models\SiteSection;
use App\Models\SocialLink;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Mail;
use Illuminate\View\View;

class ContactController extends Controller
{
    public function show(): View
    {
        $sections = SiteSection::query()
            ->with(['media', 'fieldValues'])
            ->where('page_key', 'contacto')
            ->where('is_active', true)
            ->orderBy('sort_order')
            ->get()
            ->keyBy('section_key');

        $headerLogo = MediaAsset::query()->where('path', 'uploads/branding/logoheader.png')->first();

        return view('web.contact.show', [
            'hero' => $sections->get('hero'),
            'sections' => $sections,
            'settings' => ContactPageSetting::query()->first(),
            'headerLogo' => $headerLogo,
            'headerLogoUrl' => $headerLogo ? media_asset_url($headerLogo) : null,
            'footer' => FooterSetting::query()->with('logo')->first(),
            'social_links' => SocialLink::query()->where('location', 'footer')->where('is_active', true)->orderBy('sort_order')->get(),
        ]);
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'first_name' => ['required', 'string', 'max:255'],
            'last_name' => ['required', 'string', 'max:255'],
            'email' => ['required', 'email'],
            'phone' => ['nullable', 'string', 'max:255'],
            'message' => ['nullable', 'string'],
        ]);

        $contactRequest = ContactRequest::create([
            'first_name' => cms_plain_text($validated['first_name']) ?? '',
            'last_name' => cms_plain_text($validated['last_name']) ?? '',
            'email' => cms_email($validated['email']) ?? '',
            'phone' => cms_phone($validated['phone'] ?? null),
            'message' => cms_plain_text($validated['message'] ?? null),
        ]);

        cms_security_log('info', 'Public contact form submitted.', [
            'contact_request_id' => $contactRequest->id,
        ], $request);

        app()->terminating(function () use ($contactRequest): void {
            $this->sendContactNotification($contactRequest);
        });

        return redirect()
            ->route('web.contact.show')
            ->with('success', 'Tu mensaje fue enviado correctamente.');
    }

    protected function sendContactNotification(ContactRequest $contactRequest): void
    {
        $to = $this->resolveContactNotificationRecipients();

        if ($to->isEmpty()) {
            Log::warning('Contact request notification skipped because no recipient email is configured.', [
                'contact_request_id' => $contactRequest->id,
            ]);

            return;
        }

        $adminUrl = cms_admin_login_url();

        try {
            Mail::to($to->all())->send(new ContactRequestNotification($contactRequest, $adminUrl));

            Log::info('Contact request notification sent successfully.', [
                'contact_request_id' => $contactRequest->id,
                'recipients' => $to->all(),
            ]);
        } catch (\Throwable $exception) {
            Log::error('Contact request notification failed to send.', [
                'contact_request_id' => $contactRequest->id,
                'recipients' => $to->all(),
                'message' => $exception->getMessage(),
            ]);
        }
    }

    protected function resolveContactNotificationRecipients()
    {
        return collect([
            cms_email(config('mail.contact_request.to')),
            cms_email(config('mail.contact_request.cc')),
        ])->filter()->unique()->values();
    }
}
