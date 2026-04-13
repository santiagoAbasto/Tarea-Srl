<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\RateLimiter;
use Illuminate\Support\Str;
use Illuminate\Validation\ValidationException;
use Symfony\Component\HttpFoundation\Response;

class ProtectPublicForms
{
    public function handle(Request $request, Closure $next): Response
    {
        $honeypotField = (string) config('security.forms.honeypot_field', 'website');
        $honeypotValue = trim((string) $request->input($honeypotField));

        if ($honeypotValue !== '') {
            cms_security_log('warning', 'Blocked public form submission by honeypot.', [
                'route' => $request->route()?->getName(),
                'attempted_email' => cms_email(Str::lower(trim((string) $request->input('email')))),
            ], $request);

            return $this->fakeSuccessResponse($request);
        }

        $email = Str::lower(trim((string) $request->input('email')));
        $key = sprintf(
            'public-form:%s:%s:%s',
            $request->route()?->getName() ?? $request->path(),
            $email !== '' ? $email : 'guest',
            $request->ip()
        );

        $maxAttempts = (int) config('security.forms.max_attempts', 5);
        $decaySeconds = (int) config('security.forms.decay_seconds', 60);

        if (RateLimiter::tooManyAttempts($key, $maxAttempts)) {
            cms_security_log('warning', 'Blocked public form submission by form rate limiter.', [
                'route' => $request->route()?->getName(),
                'attempted_email' => $email,
            ], $request);

            throw ValidationException::withMessages([
                'email' => 'Has realizado demasiados envios. Intenta nuevamente en unos minutos.',
            ]);
        }

        RateLimiter::hit($key, $decaySeconds);

        return $next($request);
    }

    protected function fakeSuccessResponse(Request $request): Response
    {
        if ($request->expectsJson()) {
            return response()->json([
                'ok' => true,
                'message' => 'Tu solicitud fue enviada correctamente. Te contactaremos a la brevedad.',
            ]);
        }

        return redirect()->back()->with('success', 'Tu solicitud fue enviada correctamente. Te contactaremos a la brevedad.');
    }
}
