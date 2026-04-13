<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\ContactPageSetting;
use App\Models\ContactRequest;
use App\Models\FooterSetting;
use App\Models\SiteSection;
use Inertia\Inertia;
use Inertia\Response;

class ContactController extends Controller
{
    public function index(): Response
    {
        $hero = SiteSection::query()->firstOrCreate(
            ['page_key' => 'contacto', 'section_key' => 'hero'],
            ['title' => 'CONTACTO', 'sort_order' => 'A', 'is_active' => true]
        )->load('media');

        // ContactPageSetting puede estar vacío; en ese caso la web usa FooterSetting como fallback.
        // Aquí leemos ambos para mostrar los valores efectivos que ve el visitante.
        // Al guardar desde el admin se escribe SOLO en ContactPageSetting — el footer nunca se toca.
        $settings = ContactPageSetting::query()->firstOrCreate([]);
        $footer   = FooterSetting::query()->first();

        $defaultMapIframe = '<iframe src="https://www.google.com/maps/embed?pb=!1m14!1m8!1m3!1d3276.0352701280444!2d-58.4384772!3d-34.8050545!3m2!1i1024!2i768!4f13.1!3m3!1m2!1s0x95bcd393e0e97a97%3A0x22dafa76979e211f!2sTarea%20Sociedad%20de%20Responsabilidad%20Limitada!5e0!3m2!1ses!2sar!4v1775757906240!5m2!1ses!2sar" width="600" height="450" style="border:0;" allowfullscreen="" loading="lazy" referrerpolicy="no-referrer-when-downgrade"></iframe>';
        $defaultMapLink   = 'https://maps.app.goo.gl/FFiEn78PLBZWdKep8';

        $requests = ContactRequest::query()
            ->orderByDesc('created_at')
            ->get()
            ->map(fn (ContactRequest $request) => [
                'id'         => $request->id,
                'first_name' => $request->first_name,
                'last_name'  => $request->last_name,
                'email'      => $request->email,
                'phone'      => $request->phone,
                'message'    => $request->message,
                'is_read'    => $request->is_read,
                'status'     => $request->status,
                'reply_url'  => $request->reply_url,
                'created_at' => optional($request->created_at)?->format('d/m/Y H:i'),
            ])
            ->values();

        return Inertia::render('Admin/Contacts/Index', [
            'hero' => [
                'id'        => $hero->id,
                'title'     => $hero->title,
                'media_id'  => $hero->media_id,
                'media_url' => media_asset_url($hero->media),
                'is_active' => $hero->is_active,
            ],
            'settings' => [
                'id'              => $settings->id,
                'address'         => $settings->address         ?: $footer?->contact_address ?: '',
                'phone_primary'   => $settings->phone_primary   ?: $footer?->phone_primary   ?: '',
                'phone_secondary' => $settings->phone_secondary ?: $footer?->phone_secondary ?: '',
                'email_primary'   => $settings->email_primary   ?: $footer?->email_primary   ?: '',
                'email_secondary' => $settings->email_secondary ?: $footer?->email_secondary ?: '',
                'map_link'        => $settings->map_link        ?: $defaultMapLink,
                'map_iframe'      => cms_map_iframe($settings->map_iframe) ?: $defaultMapIframe,
            ],
            'requests'   => $requests,
            'stats'      => [
                'total'  => ContactRequest::query()->count(),
                'unread' => ContactRequest::query()->where('is_read', false)->count(),
            ],
            'initialTab' => request()->string('tab')->toString() === 'requests'
                ? 'requests'
                : 'page',
            'publicContactUrl' => route('web.contact.show'),
        ]);
    }


    public function settingsPage(): Response
    {
        $settings = ContactPageSetting::query()->firstOrCreate([]);

        return Inertia::render('Admin/Contacts/Settings', [
            'settings' => [
                'id'              => $settings->id,
                'address'         => $settings->address,
                'phone_primary'   => $settings->phone_primary,
                'phone_secondary' => $settings->phone_secondary,
                'email_primary'   => $settings->email_primary,
                'email_secondary' => $settings->email_secondary,
                'map_iframe'      => cms_map_iframe($settings->map_iframe),
                'map_link'        => $settings->map_link,
            ],
        ]);
    }

    public function requestsPage(): Response
    {
        $requests = ContactRequest::query()
            ->orderByDesc('created_at')
            ->get()
            ->map(fn (ContactRequest $request) => [
                'id'         => $request->id,
                'first_name' => $request->first_name,
                'last_name'  => $request->last_name,
                'email'      => $request->email,
                'phone'      => $request->phone,
                'message'    => $request->message,
                'is_read'    => $request->is_read,
                'status'     => $request->status,
                'reply_url'  => $request->reply_url,
                'created_at' => optional($request->created_at)?->format('d/m/Y H:i'),
            ])
            ->values();

        return Inertia::render('Admin/Contacts/Requests', [
            'requests' => $requests,
            'stats'    => [
                'total'  => ContactRequest::query()->count(),
                'unread' => ContactRequest::query()->where('is_read', false)->count(),
            ],
        ]);
    }
}
