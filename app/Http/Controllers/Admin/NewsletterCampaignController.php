<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Mail\NewsletterCampaign;
use App\Models\NewsletterSubscriber;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Mail;

class NewsletterCampaignController extends Controller
{
    public function send(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'subject' => ['required', 'string', 'max:255'],
            'body'    => ['required', 'string'],
        ]);

        // En producción se envía a todos los suscriptores activos.
        // En otros entornos (local, staging) se limita a 1 para no saturar Mailtrap sandbox.
        $query = NewsletterSubscriber::query()
            ->where('is_active', true)
            ->orderByDesc('subscribed_at');

        if (! app()->isProduction()) {
            $query->limit(1);
        }

        $recipients = $query->pluck('email');

        if ($recipients->isEmpty()) {
            return response()->json(['message' => 'No hay suscriptores activos.'], 422);
        }

        $mailable = new NewsletterCampaign($validated['subject'], $validated['body']);

        foreach ($recipients as $email) {
            Mail::to($email)->send($mailable);
        }

        $sent  = $recipients->count();
        $total = NewsletterSubscriber::query()->where('is_active', true)->count();

        $message = $sent === $total
            ? "Campaña enviada a {$sent} suscriptor" . ($sent !== 1 ? 'es' : '') . '.'
            : "Campaña enviada a {$sent} de {$total} suscriptores activos (límite de entorno de prueba).";

        return response()->json(['message' => $message, 'sent' => $sent]);
    }
}
