<?php

namespace Iwgb\OrgUk\Handler;

use Guym4c\GhostApiPhp\GhostApiException;
use Guym4c\GhostApiPhp\Model as Cms;
use Iwgb\OrgUk\Intl\IntlCmsResource;
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

        $fallbackPage = Cms\Page::bySlug($this->cms, $args['page'] ?? 'covid-19');
        if (
            empty($fallbackPage)
            || $fallbackPage->primaryTag->slug !== 'covid-19'
        ) {
            throw new HttpNotFoundException($request);
        }

        $pageGroup = new IntlCmsResource($this->cms, $this->intl, $fallbackPage);

        return $this->render($request, $response,
            'covid/covid.html.twig',
            $pageGroup->getIntl()->title ??
            $pageGroup->getFallback()->title,
            [
                'pageGroup' => $pageGroup,
                'jason' => $isLanding,
            ],
        );
    }
}