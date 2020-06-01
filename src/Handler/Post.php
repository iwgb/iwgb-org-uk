<?php

namespace Iwgb\OrgUk\Handler;

use Guym4c\GhostApiPhp\GhostApiException;
use Psr\Http\Message\ResponseInterface;
use Slim\Exception\HttpNotFoundException;
use Slim\Psr7\Request;
use Slim\Psr7\Response;

class Post extends ViewHandler {

    /**
     * @inheritDoc
     * @throws GhostApiException
     */
    public function __invoke(Request $request, Response $response, array $args): ResponseInterface {

        $postGroup = $this->cms->postBySlug($args['slug']);

        if (empty($postGroup)) {
            throw new HttpNotFoundException($request);
        }

        return $this->render($request, $response,
            'post/post.html.twig',
            $postGroup->getIntl()->title ??
                $postGroup->getFallback()->title,
            ['postGroup' => $postGroup]
        );
    }
}