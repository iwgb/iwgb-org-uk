<?php

namespace Iwgb\OrgUk\Handler;

use Guym4c\GhostApiPhp\GhostApiException;
use Guym4c\GhostApiPhp\Model as Cms;
use Iwgb\OrgUk\Intl\IntlCmsResource;
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

        $fallbackPost = Cms\Post::bySlug($this->cms, $args['slug']);
        if (empty($fallbackPost)) {
            throw new HttpNotFoundException($request);
        }

        $postGroup = new IntlCmsResource($this->cms, $this->intl, $fallbackPost);

        return $this->render($request, $response,
            'post/post.html.twig',
            $postGroup->getIntl()->title ??
                $postGroup->getFallback()->title,
            ['postGroup' => $postGroup]
        );
    }
}