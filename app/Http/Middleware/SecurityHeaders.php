<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

/**
 * Dodaje nagłówki bezpieczeństwa do każdej odpowiedzi HTTP.
 *
 * HSTS — wymusza HTTPS przez 1 rok
 * X-Frame-Options — ochrona przed clickjackingiem
 * X-Content-Type-Options — blokuje MIME-sniffing
 * Referrer-Policy — ogranicza co przeglądarka wysyła w Referer
 * Permissions-Policy — kontrola dostępu do API (mikrofon, kamera, geolokacja)
 * CSP — ogranicza skąd można ładować JS/CSS/images (relaxed dla CDN fontów + AI services)
 */
class SecurityHeaders
{
    public function handle(Request $request, Closure $next): Response
    {
        $response = $next($request);

        // HSTS — wymusza HTTPS (1 rok)
        $response->headers->set('Strict-Transport-Security', 'max-age=31536000; includeSubDomains');

        // Ochrona przed clickjackingiem
        $response->headers->set('X-Frame-Options', 'SAMEORIGIN');

        // Blokada MIME sniffing
        $response->headers->set('X-Content-Type-Options', 'nosniff');

        // Kontrola referer
        $response->headers->set('Referrer-Policy', 'strict-origin-when-cross-origin');

        // Permissions Policy — mikrofon dla Web Speech API, reszta off
        $response->headers->set(
            'Permissions-Policy',
            'geolocation=(), microphone=(self), camera=(), payment=()'
        );

        // CSP — relaxed, pozwala fonts.bunny.net + self
        // Można zaostrzyć później; teraz priorytet jest żeby nie zepsuć UI
        $response->headers->set(
            'Content-Security-Policy',
            "default-src 'self'; "
            . "script-src 'self' 'unsafe-inline' 'unsafe-eval'; "
            . "style-src 'self' 'unsafe-inline' https://fonts.bunny.net; "
            . "font-src 'self' https://fonts.bunny.net data:; "
            . "img-src 'self' data: https:; "
            . "connect-src 'self' https://api.anthropic.com https://www.motorcheck.ie; "
            . "frame-ancestors 'self';"
        );

        return $response;
    }
}
