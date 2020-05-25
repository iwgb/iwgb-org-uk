<?php

namespace Iwgb\OrgUk\Handler;

use Psr\Http\Message\ResponseInterface;
use Slim\Psr7\Request;
use Slim\Psr7\Response;

class RedirectToJobType extends ViewHandler {

    private const ONLINE_JOINING_BASE_ENTRY_POINT = 'https://members.iwgb.org.uk/join/';

    /**
     * @inheritDoc
     */
    public function __invoke(Request $request, Response $response, array $routeParams): ResponseInterface {
        return $this->render($request, $response,
            'redirect/redirect.html.twig',
            $this->intl->getText('join', 'title'),
            [
                'url' => self::ONLINE_JOINING_BASE_ENTRY_POINT . $routeParams['jobType'],
                'meta'         => [
                    'title' => "{$this->intl->getText('join', 'title')} - IWGB",
                    'image' => $this->settings['defaultImage'],
                ],
            ],
        );
    }
}