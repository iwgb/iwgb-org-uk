<?php

namespace Iwgb\OrgUk\Handler;

use Psr\Http\Message\ResponseInterface;
use Slim\Psr7\Request;
use Slim\Psr7\Response;

class PressReleases extends ViewHandler {

    private const PAGE_SIZE = 12;

    /**
     * {@inheritDoc}
     */
    public function __invoke(Request $request, Response $response, array $args): ResponseInterface {

        $page = empty($args['page']) || !is_numeric($args['page'])
            ? 1
            : round($args['page']);

        $stories = $this->cms->listPosts(
            "press-releases-{$page}",
            self::PAGE_SIZE,
            $this->cms->withLanguage()
                ->and('tag', '=', 'press-release'),
            $page,
        );

        $showNext = count($stories) === self::PAGE_SIZE;

        return $this->render($request, $response,
            'feed.html.twig',
            $this->intl->getText('feed', 'press-releases'),
            [
                'stories' => $stories,
                'page'    => $page,
                'tag'     => 'press-releases',
                'showNext'=> $showNext,
                'showArchives' => !$showNext,
            ],
        );
    }
}