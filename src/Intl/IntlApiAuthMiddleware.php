<?php

namespace Iwgb\OrgUk\Intl;

use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use Psr\Http\Server\MiddlewareInterface;
use Psr\Http\Server\RequestHandlerInterface;
use Slim\Psr7\Response;
use Teapot\StatusCode;

class IntlApiAuthMiddleware implements MiddlewareInterface {

    private string $key;

    public function __construct(string $key) {
        $this->key = $key;
    }

    public function process(ServerRequestInterface $request, RequestHandlerInterface $handler): ResponseInterface {
        $key = explode(' ', $request->getHeader('Authorization')[0] ?? '')[1] ?? '';
        if ($key !== $this->key) {
            return (new Response())
                ->withStatus(StatusCode::UNAUTHORIZED);
        } else {
            return $handler->handle($request);
        }
    }
}