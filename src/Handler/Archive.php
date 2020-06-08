<?php


namespace Iwgb\OrgUk\Handler;


use DateTime;
use Exception;
use Iwgb\OrgUk\Intl\CmsResource;
use Parsedown;
use Psr\Http\Message\ResponseInterface;
use Slim\Exception\HttpNotFoundException;
use Slim\Psr7\Request;
use Slim\Psr7\Response;

class Archive extends ViewHandler {

    private const PAGE_SIZE = 12;

    /**
     * {@inheritDoc}
     * @throws Exception
     */
    public function __invoke(Request $request, Response $response, array $args): ResponseInterface {

        $page = empty($args['page']) || !is_numeric($args['page'])
            ? 1
            : round($args['page']);

        $json = array_slice(
            json_decode(file_get_contents(APP_ROOT . '/var/archive.json'), true),
            ($page - 1) * self::PAGE_SIZE,
            self::PAGE_SIZE,
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

        if (count($json) === 0) {
            throw new HttpNotFoundException($request);
        }

        return $this->render($request, $response,
            'feed.html.twig',
            'Archive',
            [
                'stories' => $posts,
                'page' => $page,
                'tag' => 'archives',
                'data' => $posts,
                'showNext' => count($posts) === self::PAGE_SIZE,
                'showAuthors' => false,
            ],
        );
    }
}