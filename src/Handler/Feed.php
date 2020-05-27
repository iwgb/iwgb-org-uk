<?php

namespace Iwgb\OrgUk\Handler;

use Guym4c\GhostApiPhp\Filter;
use Guym4c\GhostApiPhp\GhostApiException;
use Guym4c\GhostApiPhp\Model as Cms;
use Guym4c\GhostApiPhp\Sort;
use Guym4c\GhostApiPhp\SortOrder;
use Iwgb\OrgUk\Intl\IntlCmsResource;
use Psr\Http\Message\ResponseInterface;
use Slim\Exception\HttpNotFoundException;
use Slim\Psr7\Request;
use Slim\Psr7\Response;

class Feed extends ViewHandler {

    private const ALLOWED_TAGS = [
        'press-releases' => 'press-release',
        'blog'          => 'blog',
        'all'           => 'all',
    ];

    /**
     * {@inheritDoc}
     * @throws GhostApiException
     */
    public function __invoke(Request $request, Response $response, array $args): ResponseInterface {

        if (!in_array($args['tag'], array_keys(self::ALLOWED_TAGS), true)) {
            throw new HttpNotFoundException($request);
        }

        $filter = $args['tag'] !== 'all'
            ? (new Filter())->by('tag', '=', self::ALLOWED_TAGS[$args['tag']])
            : null;

        $page = empty($args['page']) || !is_numeric($args['page'])
            ? 1
            : round($args['page']);

        $stories = IntlCmsResource::getIntlResources($this->cms, $this->intl, Cms\Post::get($this->cms, 9,
            new Sort('published_at', SortOrder::DESC),
            $filter,
            $page
        )->getResources());

        return $this->render($request, $response,
            'feed.html.twig',
            $this->intl->getText('feed', $args['tag']),
            [
                'stories' => $stories,
                'page'    => $page,
                'tag'     => $args['tag'],
                'showNext'=> count($stories) === 9,
            ],
        );
    }
}