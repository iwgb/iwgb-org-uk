<?php

namespace Iwgb\OrgUk\Handler;

use Psr\Http\Message\ResponseInterface;
use Slim\Exception\HttpException;
use Slim\Exception\HttpNotFoundException;
use Slim\Handlers\ErrorHandler as SlimErrorHandler;
use Teapot\StatusCode;
use Sentry;

class ErrorHandler extends SlimErrorHandler {

    protected function respond(): ResponseInterface {

        $exception = $this->exception;
        if ($exception instanceof HttpException) {
            if ($exception instanceof HttpNotFoundException) {
                $response = $this->responseFactory->createResponse(StatusCode::NOT_FOUND);
                $response->getBody()->write('not found');
                return $response;
            }
        } else {
            if (!in_array($_ENV['ENVIRONMENT'], ['dev', 'qa'])) {
                Sentry\captureException($this->exception);
            }
        }

        return parent::respond();
    }
}