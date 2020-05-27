<?php

namespace Iwgb\OrgUk;

use Psr\Http\Message\ResponseInterface;
use Teapot\StatusCode;

class Psr7Utils {

    public static function redirect(ResponseInterface $response, string $uri, int $statusCode = StatusCode::FOUND) {
        return $response
            ->withHeader('Location', $uri)
            ->withStatus($statusCode);
    }
}