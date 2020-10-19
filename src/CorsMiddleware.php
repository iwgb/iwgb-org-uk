<?php

namespace Iwgb\OrgUk;

use Tuupola\Middleware\CorsMiddleware as Cors;

class CorsMiddleware {

    public static function withOptions(array $options = []): Cors {
        return new Cors(array_merge([
            'origin' => ['*'],
            'methods' => ['GET', 'POST', 'OPTIONS'],
            'headers.allow' => [
                'Authorization',
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
        ], $options));
    }
}