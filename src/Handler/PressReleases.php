<?php

namespace Iwgb\OrgUk\Handler;

use DateTime;
use Exception;
use Guym4c\GhostApiPhp\Model as Ghost;
use Iwgb\OrgUk\Intl\CmsResource;
use Parsedown;
use Psr\Http\Message\ResponseInterface;
use Slim\Exception\HttpNotFoundException;
use Slim\Psr7\Request;
use Slim\Psr7\Response;

class PressReleases extends ViewHandler {

    private const PAGE_SIZE = 12;

    /**
     * {@inheritDoc}
     * @throws Exception
     */
    public function __invoke(Request $request, Response $response, array $args): ResponseInterface {

        $page = empty($args['page']) || !is_numeric($args['page'])
            ? 1
            : (int) round($args['page']);

        $onlyPressReleases = $this->cms->withLanguage()
            ->and('tag', '=', 'press-release');

        $numPosts = $this->cache->get(
            'total-press-releases',
            fn(): int => Ghost\Post::get($this->ghost, 1, null, $onlyPressReleases)
                ->getNumPages()
        );

        $stories = $this->cms->listPosts(
            "press-releases-{$page}",
            self::PAGE_SIZE,
            $onlyPressReleases,
            $page,
        );

        if (empty($stories)) {
            $completePages = (int) floor($numPosts / self::PAGE_SIZE);
            $cmsPostsOnLastPage = $numPosts % self::PAGE_SIZE;

            $offset = (($page - $completePages - 1) * self::PAGE_SIZE) - $cmsPostsOnLastPage;

            $stories = $this->getArchivedPosts($offset);
        } else if (count($stories) < self::PAGE_SIZE) {
            $stories = [
                ...$stories,
                ...$this->getArchivedPosts(0, self::PAGE_SIZE - count($stories)),
            ];
        }

        if (empty($stories)) {
            throw new HttpNotFoundException($request);
        }

        $showNext = count($stories) === self::PAGE_SIZE;

        return $this->render($request, $response,
            'feed.html.twig',
            $this->intl->getText('feed', 'press-releases'),
            [
                'stories' => $stories,
                'page'    => $page,
                'tag'     => 'press-releases',
                'showNext'=> $showNext,
                'showEnd' => !$showNext,
                'showAuthors' => false,
            ],
        );
    }

    /**
     * @param int $offset
     * @param int $length
     * @return CmsResource[]
     * @throws Exception
     */
    private function getArchivedPosts(int $offset, int $length = self::PAGE_SIZE): array {
        $json = array_slice(
            json_decode(file_get_contents(APP_ROOT . '/var/archive.json'), true),
            $offset,
            $length,
        );

        $posts = [];
        foreach ($json as $id => $post) {
            $posts[] = CmsResource::fromLegacyResource($this->ghost, $this->intl, [
                'title'         => $post['title'],
                'publishedAt'   => new DateTime($post['timestamp']),
                'featureImage'  => $post['header_img'] !== '' ? "https://cdn.iwgb.org.uk/{$post['header_img']}" : null,
                'html'          => (new Parsedown())->text($post['content']),
                'primaryAuthor' => ['name' => 'IWGB Staff'],
                'slug'          => "{$id}/archived",
            ]);
        }

        return $posts;
    }
}