<?php

namespace Iwgb\OrgUk\Handler;

use DateTime;
use Exception;
use Iwgb\OrgUk\Psr7Utils as Psr7;
use Parsedown;
use Psr\Http\Message\ResponseInterface;
use Slim\Exception\HttpNotFoundException;
use Slim\Psr7\Request;
use Slim\Psr7\Response;
use Teapot\StatusCode;

class LegacyPost extends ViewHandler {

    /**
     * @inheritDoc
     * @throws Exception
     */
    public function __invoke(Request $request, Response $response, array $args): ResponseInterface {

        $post = json_decode(file_get_contents(APP_ROOT . '/var/archive.json'), true)[$args['id']] ?? null;

        if (empty($post)) {
            throw new HttpNotFoundException($request);
        }

        if (!empty($post['redirect'])) {
            Psr7::redirect($response, $post['redirect'], StatusCode::MOVED_PERMANENTLY);
        }

        return $this->render($request, $response,
            'post/post.html.twig',
            $post['title'],
            ['post' => [
                'title'         => $post['title'],
                'publishedAt'   => new DateTime($post['timestamp']),
                'featureImage'  => "https://cdn.iwgb.org.uk/{$post['header_img']}",
                'html'          => (new Parsedown())->text($post['content']),
                'primaryAuthor' => ['name' => 'IWGB Staff'],
            ]],
        );
    }
}