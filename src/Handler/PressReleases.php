<?php

namespace Iwgb\OrgUk\Handler;

use Psr\Http\Message\ResponseInterface;
use Slim\Psr7\Request;
use Slim\Psr7\Response;

class PressReleases extends ViewHandler {

    /**
     * {@inheritDoc}
     */
    public function __invoke(Request $request, Response $response, array $args): ResponseInterface {

        $page = empty($args['page']) || !is_numeric($args['page'])
            ? 1
            : round($args['page']);

        $stories = $this->cms->listPosts(
            "press-releases-{$page}",
            9,
            $this->cms->withLanguage()
                ->and('tag', '=', 'press-release'),
            $page,
        );

        return $this->render($request, $response,
            'feed.html.twig',
            $this->intl->getText('feed', 'press-releases'),
            [
                'stories' => $stories,
                'page'    => $page,
                'tag'     => 'press-releases',
                'showNext'=> count($stories) === 9,
            ],
        );
    }
}