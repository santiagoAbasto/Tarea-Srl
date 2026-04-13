<?php

namespace App\Http\Controllers\Web;

use App\Http\Controllers\Controller;
use App\Models\NewsletterSubscriber;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class NewsletterController extends Controller
{
    public function store(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'email' => ['required', 'email', 'max:255'],
        ]);

        $email = cms_email($validated['email']);

        $subscriber = NewsletterSubscriber::query()->firstOrNew([
            'email' => $email,
        ]);

        $wasExisting = $subscriber->exists;

        $subscriber->is_active = true;
        $subscriber->subscribed_at = now();
        $subscriber->save();

        cms_security_log('info', 'Newsletter subscription stored.', [
            'subscriber_id' => $subscriber->id,
            'target_email' => $email,
        ], $request);

        return response()->json([
            'ok' => true,
            'message' => $wasExisting
                ? 'Este email ya estaba suscripto. Lo reactivamos correctamente.'
                : 'Suscripcion exitosa.',
        ]);
    }
}
