<?php

namespace Iwgb\OrgUk\Handler;

use Psr\Http\Message\ResponseInterface;
use Slim\Exception\HttpException;
use Slim\Exception\HttpNotFoundException;
use Slim\Handlers\ErrorHandler as SlimErrorHandler;
use Slim\Psr7;
use Teapot\StatusCode;
use Sentry;

class ErrorHandler extends SlimErrorHandler {

    protected function respond(): ResponseInterface {

        $exception = $this->exception;
        if ($exception instanceof HttpException) {
            if ($exception instanceof HttpNotFoundException) {
                return $this->respondWithStaticView(StatusCode::NOT_FOUND, 'notFound.html', true);
            }
        } else {
            if (!in_array($_ENV['ENVIRONMENT'], ['dev', 'qa'])) {
                Sentry\captureException($this->exception);
                return $this->respondWithStaticView(StatusCode::INTERNAL_SERVER_ERROR, 'error.html');
            }
        }

        return parent::respond();
    }

    private function respondWithStaticView(int $status, string $htmlFilePath, bool $redirect = false): ResponseInterface {
        if ($redirect) {
            $response = $this->responseFactory->createResponse($status);
            $response->getBody()->write(str_replace(
                '%uri%',
                $this->request->getUri()->getPath(),
                file_get_contents(APP_ROOT . "/view/{$htmlFilePath}"),
            ));
            return $response;
        } else {
            return $this->responseFactory
                ->createResponse($status)
                ->withBody(new Psr7\Stream(fopen(APP_ROOT . "/view/{$htmlFilePath}", 'r')));
        }
    }
}