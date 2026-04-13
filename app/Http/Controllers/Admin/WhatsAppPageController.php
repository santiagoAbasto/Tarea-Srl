<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\FooterSetting;
use Inertia\Inertia;
use Inertia\Response;

class WhatsAppPageController extends Controller
{
    public function index(): Response
    {
        $setting = FooterSetting::query()->firstOrCreate([]);

        return Inertia::render('Admin/WhatsApp/Index', [
            'whatsappUrl' => $setting->whatsapp_url ?? '',
        ]);
    }
}
