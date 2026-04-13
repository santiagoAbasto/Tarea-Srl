<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class SecurityHeaders
{
    public function handle(Request $request, Closure $next): Response
    {
        /** @var Response $response */
        $response = $next($request);

        $response->headers->set('Content-Security-Policy', $this->contentSecurityPolicy());
        $response->headers->set('X-Frame-Options', 'SAMEORIGIN');
        $response->headers->set('X-Content-Type-Options', 'nosniff');
        $response->headers->set('Referrer-Policy', 'strict-origin-when-cross-origin');
        $response->headers->set('Permissions-Policy', 'camera=(), microphone=(), geolocation=()');
        $response->headers->set('Cross-Origin-Resource-Policy', 'same-origin');
        $response->headers->set('X-Permitted-Cross-Domain-Policies', 'none');
        $response->headers->set('Origin-Agent-Cluster', '?1');

        if ($request->isSecure() && app()->environment('production')) {
            $response->headers->set('Strict-Transport-Security', 'max-age=31536000; includeSubDomains');
        }

        return $response;
    }

    protected function contentSecurityPolicy(): string
    {
        $viteOrigins = $this->viteOrigins();
        $scriptSrc = array_merge(["'self'", "'unsafe-inline'"], $viteOrigins);
        $styleSrc = array_merge(["'self'", "'unsafe-inline'", 'https:'], $viteOrigins);
        $connectSrc = array_merge(["'self'", 'https:'], $viteOrigins, ['ws:', 'wss:']);

        $directives = [
            "default-src 'self'",
            "base-uri 'self'",
            "form-action 'self'",
            "frame-ancestors 'self'",
            "object-src 'none'",
            "script-src-attr 'none'",
            'img-src \'self\' data: blob: https:',
            'font-src \'self\' data: https:',
            'media-src \'self\' data: blob: https:',
            'frame-src \'self\' https://www.youtube.com https://www.youtube-nocookie.com https://www.google.com https://www.google.com/maps https://maps.google.com',
            'script-src ' . implode(' ', array_unique($scriptSrc)),
            'style-src ' . implode(' ', array_unique($styleSrc)),
            'connect-src ' . implode(' ', array_unique($connectSrc)),
        ];

        return implode('; ', $directives);
    }

    /**
     * @return array<int, string>
     */
    protected function viteOrigins(): array
    {
        $hotFile = public_path('hot');

        if (! is_file($hotFile)) {
            return [];
        }

        $origin = trim((string) file_get_contents($hotFile));

        return $origin === '' ? [] : [$origin];
    }
}
