<?php

namespace Iwgb\OrgUk;

use Psr\Http\Message\ResponseInterface;
use stdClass;
use Teapot\StatusCode;

class Psr7Utils {

    public static function redirect(ResponseInterface $response, string $uri, int $statusCode = StatusCode::FOUND) {
        return $response
            ->withHeader('Location', $uri)
            ->withStatus($statusCode);
    }

    public static function withJson(ResponseInterface $response, array $data = []): ResponseInterface {
        $json = json_encode($data === []
            ? new stdClass()
            : $data
        );

        $response->getBody()
            ->write($json);

        return $response
            ->withHeader('Content-Type', 'application/json')
            ->withHeader('Content-Length', mb_strlen($json, '8bit'));
    }
}