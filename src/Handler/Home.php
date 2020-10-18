<?php

namespace Iwgb\OrgUk\Handler;

use Guym4c\Airtable\AirtableApiException;
use Guym4c\GhostApiPhp\Filter;
use Guym4c\GhostApiPhp\GhostApiException;
use Psr\Http\Message\ResponseInterface;
use Slim\Psr7\Request;
use Slim\Psr7\Response;

class Home extends ViewHandler {

    /**
     * {@inheritDoc}
     * @throws GhostApiException
     * @throws AirtableApiException
     */
    public function __invoke(Request $request, Response $response, array $args): ResponseInterface {

        $featured = $this->cms->listPosts(
            'home-featured',
            1,
            $this->cms->withLanguage()
                ->and('featured', '=', 'true', true)
                ->and('tag', '=', 'press-release'),
        )[0];

        $noMainFeaturePosts = (new Filter())
            ->by('comment_id', '-', $featured->getFallback()->commentId);

        if (!empty($featured->getIntl())) {
            $noMainFeaturePosts->and('comment_id', '-', $featured->getIntl()->commentId);
        }

        return $this->render($request, $response,
            'home/home.html.twig',
            $this->intl->getText('home', 'slogan'),
            [
                'slideshow' => [
                    'https://cdn.iwgb.org.uk/bucket/home/header1.jpg',
                    'https://cdn.iwgb.org.uk/bucket/home/header2.jpg',
                ],
                'mapFooter' => true,
                'featured'  => $featured,

                'posts' => $this->cms->listPosts(
                    'home-feed',
                    3,
                    $this->cms->withLanguage()
                        ->and('tag', '=', 'press-release')
                        ->with($noMainFeaturePosts)
                ),

                'campaigns' => $this->cms->listPages(
                    'home-campaigns',
                    null,
                    (new Filter())
                        ->by('tag', '=', $this->intl->getLanguage())
                        ->and('tag', '=', 'category-campaign')
                        ->and('featured', '=', 'true', true)
                ),

                'contacts' => $this->settings['contacts'],
                'meta' => [
                    'title' => "IWGB: {$this->intl->getText('home', 'slogan')}",
                    'image' => $this->settings['defaultImage'],
                    'description' => $this->intl->getText('home', 'seoDescription'),
                ],
            ],
        );
    }
}