<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\ContactPageSetting;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class ContactPageSettingController extends Controller
{
    public function show(): JsonResponse
    {
        $setting = ContactPageSetting::query()->firstOrCreate([]);

        return response()->json([
            'id' => $setting->id,
            'address' => cms_plain_text($setting->address),
            'phone_primary' => cms_phone($setting->phone_primary),
            'phone_secondary' => cms_phone($setting->phone_secondary),
            'email_primary' => cms_email($setting->email_primary),
            'email_secondary' => cms_email($setting->email_secondary),
            'map_iframe' => cms_map_iframe($setting->map_iframe),
            'map_link' => \App\Support\CmsSecurity::sanitizeUrl($setting->map_link),
        ]);
    }

    public function update(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'address' => ['nullable', 'string'],
            'phone_primary' => ['nullable', 'string', 'max:255'],
            'phone_secondary' => ['nullable', 'string', 'max:255'],
            'email_primary' => ['nullable', 'email', 'max:255'],
            'email_secondary' => ['nullable', 'email', 'max:255'],
            'map_iframe' => ['nullable', 'string'],
            'map_link' => ['nullable', 'string', 'max:1000'],
        ]);

        $setting = ContactPageSetting::query()->firstOrCreate([]);
        $setting->fill([
            'address' => cms_plain_text($validated['address'] ?? null),
            'phone_primary' => cms_phone($validated['phone_primary'] ?? null),
            'phone_secondary' => cms_phone($validated['phone_secondary'] ?? null),
            'email_primary' => cms_email($validated['email_primary'] ?? null),
            'email_secondary' => cms_email($validated['email_secondary'] ?? null),
            'map_iframe' => cms_map_iframe($validated['map_iframe'] ?? null),
            'map_link' => \App\Support\CmsSecurity::sanitizeUrl($validated['map_link'] ?? null),
        ])->save();

        cms_security_log('info', 'Contact page settings updated.', [
            'contact_page_setting_id' => $setting->id,
        ], $request);

        return response()->json($setting);
    }
}
