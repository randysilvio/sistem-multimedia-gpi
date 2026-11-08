<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

/**
 * Header keamanan dasar untuk aplikasi yang diakses dari internet.
 * - Kamera hanya boleh dipakai halaman ini sendiri (latar kamera di layar live).
 * - Halaman tidak bisa dibingkai situs lain (clickjacking).
 * - HSTS hanya dikirim lewat HTTPS di lingkungan produksi.
 */
class SecurityHeaders
{
    public function handle(Request $request, Closure $next): Response
    {
        $response = $next($request);
        $h = $response->headers;

        $h->set('X-Content-Type-Options', 'nosniff');
        $h->set('X-Frame-Options', 'SAMEORIGIN');
        $h->set('Referrer-Policy', 'strict-origin-when-cross-origin');
        $h->set('Permissions-Policy', 'camera=(self), microphone=(), geolocation=(), payment=(), usb=()');
        $h->set('Cross-Origin-Opener-Policy', 'same-origin');

        if ($request->isSecure() && app()->environment('production')) {
            $h->set('Strict-Transport-Security', 'max-age=31536000; includeSubDomains');
        }

        // Halaman yang butuh login jangan disimpan cache browser/proxy bersama
        if ($request->user() && !$response instanceof \Symfony\Component\HttpFoundation\BinaryFileResponse) {
            $h->set('Cache-Control', 'no-store, private');
        }

        $h->remove('X-Powered-By');

        return $response;
    }
}
