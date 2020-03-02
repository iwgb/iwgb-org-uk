<?php

namespace Iwgb\OrgUk\Handler;

use Guym4c\GhostApiPhp\Filter;
use Guym4c\GhostApiPhp\Model as Cms;
use Guym4c\GhostApiPhp\Sort;
use Guym4c\GhostApiPhp\SortOrder;
use Iwgb\OrgUk\Intl\IntlCache;
use Iwgb\OrgUk\Intl\IntlCmsResource;

class Home extends RootHandler {

    /**
     * {@inheritDoc}
     */
    public function __invoke(array $routeParams): void {

        $featured = $this->cache->get(IntlCache::FEATURED_POST, fn(): Cms\Post =>
            Cms\Post::get($this->cms, 1,
                new Sort('published_at', SortOrder::DESC),
                (new Filter())->by('featured', '=', 'true', true)
            )->getResources()[0]
        );

        $this->render('home/home.html.twig',
            $this->intl->getText('home', 'slogan'),
            [
                'slideshow' => [
                    'https://cdn.iwgb.org.uk/bucket/home/header1.jpg',
                    'https://cdn.iwgb.org.uk/bucket/home/header2.jpg',
                ],
                'mapFooter' => true,
                'featured'  => new IntlCmsResource($this->cms, $this->intl, $featured),

                'posts'     => IntlCmsResource::getIntlResources($this->cms, $this->intl, Cms\Post::get($this->cms, 3,
                    new Sort('published_at', SortOrder::DESC),
                    (new Filter())->by('comment_id', '-', $featured->commentId)
                )->getResources()),

                'campaigns' => Cms\Page::get($this->cms, null, null,
                    $this->intl->ghostFilterFactory()
                        ->and('tag', '=', 'category-campaign')
                        ->and('featured', '=', 'true', true)
                )->getResources(),
            ]
        );
    }
}