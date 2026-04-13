<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\FooterSetting;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class FooterSettingController extends Controller
{
    public function show(): JsonResponse
    {
        $setting = FooterSetting::query()->with('logo')->firstOrCreate([]);

        return response()->json($setting);
    }

    public function update(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'logo_media_id' => ['nullable', 'integer', 'exists:media_assets,id'],
            'brand_name' => ['nullable', 'string', 'max:255'],
            'newsletter_title' => ['nullable', 'string', 'max:255'],
            'newsletter_placeholder' => ['nullable', 'string', 'max:255'],
            'contact_title' => ['nullable', 'string', 'max:255'],
            'contact_address' => ['nullable', 'string'],
            'phone_primary' => ['nullable', 'string', 'max:255'],
            'phone_secondary' => ['nullable', 'string', 'max:255'],
            'email_primary' => ['nullable', 'string', 'max:255'],
            'email_secondary' => ['nullable', 'string', 'max:255'],
            'whatsapp_url' => ['nullable', 'string', 'max:255'],
            'copyright_text' => ['nullable', 'string'],
        ]);

        $setting = FooterSetting::query()->firstOrCreate([]);
        $setting->fill($validated)->save();

        return response()->json($setting->fresh('logo'));
    }
}
