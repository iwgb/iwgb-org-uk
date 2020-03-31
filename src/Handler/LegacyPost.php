<?php

namespace Iwgb\OrgUk\Handler;

use DateTime;
use Exception;
use Parsedown;
use Siler\Http\Response;

class LegacyPost extends RootHandler {

    /**
     * @inheritDoc
     * @throws Exception
     */
    public function __invoke(array $routeParams): void {

        $post = json_decode(file_get_contents(APP_ROOT . '/var/archive.json'), true)[$routeParams['id']] ?? null;

        if (empty($post)) {
            self::notFound();
            return;
        }

        if (!empty($post['redirect'])) {
            Response\redirect($post['redirect']);
            return;
        }

        $this->render('post/post.html.twig', $post['title'], ['post' => [
            'title'         => $post['title'],
            'publishedAt'   => new DateTime($post['timestamp']),
            'featureImage'  => "https://cdn.iwgb.org.uk/{$post['header_img']}",
            'html'          => (new Parsedown())->text($post['content']),
            'primaryAuthor' => ['name' => 'IWGB Staff'],
        ]]);
    }
}