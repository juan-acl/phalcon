<?php

namespace App\Library\Http;

use Phalcon\Http\ResponseInterface;

final class SecurityHeaders
{
    public static function apply(ResponseInterface $response): void
    {
        $headers = [
            'X-Frame-Options'        => 'SAMEORIGIN',
            'X-Content-Type-Options' => 'nosniff',
            'Referrer-Policy'        => 'strict-origin-when-cross-origin',
            'Permissions-Policy'     => 'camera=(), microphone=(), geolocation=()',
            'Content-Security-Policy'=> "default-src 'self'; "
                . "img-src 'self' data:; "
                . "style-src 'self' 'unsafe-inline' https://cdn.jsdelivr.net; "
                . "script-src 'self' https://cdn.jsdelivr.net; "
                . "font-src 'self' https://cdn.jsdelivr.net; "
                . "connect-src 'self' https://cdn.jsdelivr.net; "
                . "frame-ancestors 'self'",
        ];

        foreach ($headers as $k => $v) {
            $response->setHeader($k, $v);
            if (!headers_sent()) {
                header("{$k}: {$v}");
            }
        }
    }
}
