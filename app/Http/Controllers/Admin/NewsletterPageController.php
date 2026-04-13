<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\NewsletterSubscriber;
use Inertia\Inertia;
use Inertia\Response;

class NewsletterPageController extends Controller
{
    public function index(): Response
    {
        $subscribers = NewsletterSubscriber::query()
            ->orderByDesc('subscribed_at')
            ->orderByDesc('id')
            ->get()
            ->map(fn (NewsletterSubscriber $sub) => [
                'id'            => $sub->id,
                'email'         => $sub->email,
                'is_active'     => $sub->is_active,
                'subscribed_at' => optional($sub->subscribed_at)?->format('d/m/Y'),
            ])
            ->values();

        return Inertia::render('Admin/Newsletter/Index', [
            'subscribers' => $subscribers,
            'stats' => [
                'total'  => NewsletterSubscriber::query()->count(),
                'active' => NewsletterSubscriber::query()->where('is_active', true)->count(),
            ],
        ]);
    }
}
