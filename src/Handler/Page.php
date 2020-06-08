<?php

namespace Iwgb\OrgUk\Handler;

use Guym4c\GhostApiPhp\GhostApiException;
use Iwgb\OrgUk\Service\Cms;
use Psr\Http\Message\ResponseInterface;
use Slim\Exception\HttpNotFoundException;
use Slim\Psr7\Request;
use Slim\Psr7\Response;

class Page extends ViewHandler {

    /**
     * @inheritDoc
     * @throws GhostApiException
     */
    public function __invoke(Request $request, Response $response, array $args): ResponseInterface {
        $pageGroup = $this->cms->pageBySlug($args['slug']);

        if (empty($pageGroup)) {
            throw new HttpNotFoundException($request);
        }

        $relatedContent = [];
        $relatedTitleKey = 'related';
        $showAuthors = true;
        foreach ($pageGroup->getFallback()->tags as $tag) {
            if ($tag->slug === 'special-careers') {
                $relatedContent = $this->cms->listPosts(
                    'available-jobs',
                    null,
                    $this->cms->withLanguage()
                        ->and('tag', '=', 'category-job')
                );
                $relatedTitleKey = 'careers';
                $showAuthors = false;
            }
        }

        return $this->render($request, $response,
            'page/page.html.twig',
            Cms::getTitle($pageGroup),
            [
                'pageGroup' => $pageGroup,
                'relatedContent' => $relatedContent,
                'relatedTitleKey' => $relatedTitleKey,
                'showAuthors' => $showAuthors,
            ],
        );
    }
}