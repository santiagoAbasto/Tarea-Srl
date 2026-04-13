<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\SocialLink;
use Inertia\Inertia;
use Inertia\Response;

class SocialLinksPageController extends Controller
{
    public function index(): Response
    {
        $links = SocialLink::query()
            ->where('location', 'footer')
            ->orderBy('sort_order')
            ->get()
            ->map(fn (SocialLink $link) => [
                'id'        => $link->id,
                'platform'  => $link->platform,
                'label'     => $link->label,
                'url'       => $link->url,
                'icon'      => $link->icon,
                'is_active' => $link->is_active,
            ])
            ->values();

        return Inertia::render('Admin/SocialLinks/Index', [
            'links' => $links,
        ]);
    }
}
