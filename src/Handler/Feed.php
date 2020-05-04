<?php

namespace Iwgb\OrgUk\Handler;

use Guym4c\GhostApiPhp\Filter;
use Guym4c\GhostApiPhp\Model as Cms;
use Guym4c\GhostApiPhp\Sort;
use Guym4c\GhostApiPhp\SortOrder;
use Iwgb\OrgUk\Intl\IntlCmsResource;

class Feed extends RootHandler {

    private const ALLOWED_TAGS = [
        'press-releases' => 'press-release',
        'blog'          => 'blog',
        'all'           => 'all',
    ];

    public function __invoke(array $routeParams): void {

        if (!in_array($routeParams['tag'], array_keys(self::ALLOWED_TAGS), true)) {
            self::notFound();
            return;
        }

        $filter = $routeParams['tag'] !== 'all'
            ? (new Filter())->by('tag', '=', self::ALLOWED_TAGS[$routeParams['tag']])
            : null;

        $page = empty($routeParams['page']) || !is_numeric($routeParams['page'])
            ? 1
            : round($routeParams['page']);

        $stories = IntlCmsResource::getIntlResources($this->cms, $this->intl, Cms\Post::get($this->cms, 9,
            new Sort('published_at', SortOrder::DESC),
            $filter,
            $page
        )->getResources());

        $this->render('feed.html.twig', $this->intl->getText('feed', $routeParams['tag']), [
            'stories' => $stories,
            'page'    => $page,
            'tag'     => $routeParams['tag'],
            'showNext'=> count($stories) === 9,
        ]);
    }
}