<?php

namespace Iwgb\OrgUk\Handler;

use Guym4c\GhostApiPhp\GhostApiException;
use Iwgb\OrgUk\Service\Cms;
use Psr\Http\Message\ResponseInterface;
use Slim\Exception\HttpNotFoundException;
use Slim\Psr7\Request;
use Slim\Psr7\Response;

class CovidPage extends ViewHandler {

    /**
     * @inheritDoc
     * @throws GhostApiException
     */
    public function __invoke(Request $request, Response $response, array $args): ResponseInterface {

        $isLanding = empty($args['page']);

        $page = $this->cms->pageBySlug($args['page'] ?? 'covid-19');

        if (
            $page === null
            || $page->getFallback()->primaryTag->slug !== 'covid-19'
        ) {
            throw new HttpNotFoundException($request);
        }

        return $this->render($request, $response,
            'covid/covid.html.twig',
            Cms::getTitle($page),
            [
                'pageGroup' => $page,
                'jason' => $isLanding,
            ],
        );
    }
}