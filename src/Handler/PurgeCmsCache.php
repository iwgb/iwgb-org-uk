<?php

namespace Iwgb\OrgUk\Handler;

use Psr\Http\Message\ResponseInterface;
use Slim\Psr7\Request;
use Slim\Psr7\Response;
use Teapot\StatusCode;

class PurgeCmsCache extends ViewHandler {

    /**
     * {@inheritDoc}
     */
    public function __invoke(Request $request, Response $response, array $routeParams): ResponseInterface {
        if ($request->getQueryParams()['key'] !== $this->settings['cms']['purgeKey']) {
            return $response->withStatus(StatusCode::UNAUTHORIZED);
        }

        $this->cache->purge();
        $this->branches->flushCache();
        $this->membership->flushCache();
        $this->cms->flushCache();
        return $response->withStatus(StatusCode::NO_CONTENT);
    }
}