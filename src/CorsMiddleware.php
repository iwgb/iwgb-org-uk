<?php

namespace Iwgb\OrgUk;

use Tuupola\Middleware\CorsMiddleware as Cors;

class CorsMiddleware {

    public static function withOptions(): Cors {
        return new Cors([
            'origin' => ['*'],
            'methods' => ['POST', 'OPTIONS'],
            'headers.allow' => [
                'Content-Type',
                'DNT',
                'User-Agent',
                'X-Requested-With',
                'If-Modified-Since',
                'Cache-Control',
                'Range',
            ],
            'headers.expose' => [
                'Content-Length',
                'Content-Range',
            ],
        ]);
    }
}