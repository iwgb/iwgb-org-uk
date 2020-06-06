<?php

namespace Iwgb\OrgUk\Handler;

use Psr\Http\Message\ResponseInterface;
use Slim\Psr7\Request;
use Slim\Psr7\Response;

class NotFound extends ViewHandler {

    /**
     * {@inheritDoc}
     */
    public function __invoke(Request $request, Response $response, array $args): ResponseInterface {
        return $this->render($request, $response,
            'error/error.html.twig',
            $this->intl->getText('notFound', 'title'),
            [
                'type' => 'notFound',
            ]
        );
    }
}