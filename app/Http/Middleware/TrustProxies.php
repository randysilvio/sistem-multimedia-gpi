<?php

namespace App\Http\Middleware;

use Illuminate\Http\Middleware\TrustProxies as Middleware;
use Illuminate\Http\Request;

class TrustProxies extends Middleware
{
    /**
     * The trusted proxies for this application.
     *
     * @var array<int, string>|string|null
     */
    protected $proxies;

    /**
     * Proxy tepercaya dari .env (TRUSTED_PROXIES): "*" untuk Cloudflare / load balancer /
     * panel hosting yang meneruskan HTTPS, atau daftar IP dipisah koma. Kosong = tidak ada.
     */
    protected function proxies()
    {
        $value = config('app.trusted_proxies');
        if ($value === null || $value === '') {
            return $this->proxies;
        }

        return $value === '*' ? '*' : array_map('trim', explode(',', $value));
    }

    /**
     * The headers that should be used to detect proxies.
     *
     * @var int
     */
    protected $headers =
        Request::HEADER_X_FORWARDED_FOR |
        Request::HEADER_X_FORWARDED_HOST |
        Request::HEADER_X_FORWARDED_PORT |
        Request::HEADER_X_FORWARDED_PROTO |
        Request::HEADER_X_FORWARDED_AWS_ELB;
}
