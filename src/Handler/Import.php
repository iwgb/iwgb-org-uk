<?php

namespace Iwgb\OrgUk\Handler;

use Siler\Http\Response;

class Import extends RootHandler {

    private const OFFSET = 0;

    /**
     * @inheritDoc
     */
    public function __invoke(array $routeParams): void {
        $posts = array_merge(
            json_decode(file_get_contents(APP_ROOT . '/var/archive.json'), true),
            json_decode(file_get_contents(APP_ROOT . '/var/press-releases.json'), true),
        );

        $posts = array_slice($posts, self::OFFSET, 2);

        $output = [
            'meta'  => [
                'exported_on' => time(),
                'version'     => '2.14.0',
            ],
            'data' => [
                'users' => [
                    'id'    => 1,
                    'name'  => 'IWGB Staff',
                    'email' => 'office@iwgb.org.uk',
                ],
                'tags'  => [
                    [
                        'id'   => 1,
                        'name' => 'lang/en',
                        'slug' => 'lang-en',
                    ],
                    [
                        'id'   => 2,
                        'name' => 'lang/es',
                        'slug' => 'lang-es',
                    ],
                ],
            ],
        ];

        for ($i = 1; $i < count($posts); $i++) {

            $post = $posts[$i - 1];

            $markdown = $post['content'];
            $markdown = preg_replace_callback('/!\[[^\]]+\]\((?<src>[^\)]+)\)[[\n]*/',
                function (array $matches): string {
                    if (strpos($matches['src'], 'cdn.iwgb.org.uk')) {
                        return $matches[0];
                    } else {
                        return '';
                    }
                }, $markdown);

            $mobiledoc = json_decode(file_get_contents(APP_ROOT . '/var/mobiledoc.json'), true);
            $mobiledoc['cards'][1]['markdown'] = $markdown;

            $output['data']['posts'][] = [
                'id'            => $i,
                'title'         => $post['title'],
                'slug'          => $post['id'],
                'mobiledoc'     => json_encode($mobiledoc),
                'status'        => 'published',
                'published_at'  => strtotime($post['timestamp']),
                'author_id'     => 1,
                'featured'      => !empty($post['header_img']),
                'feature_image' => empty($post['header_img'])
                    ? null
                    : "https://cdn.iwgb.org.uk/${post['header_img']}",
            ];

            $output['data']['posts_tags'][] = [
                'tag_id'  => $post['language'] == 'en' ? 1 : 2,
                'post_id' => $i,
            ];
        }

        Response\json($output);
    }
}