<?php

namespace Iwgb\OrgUk\Handler;

use Guym4c\GhostApiPhp\Model as Cms;
use Iwgb\OrgUk\Intl\IntlCmsResource;

class Post extends RootHandler {

    /**
     * @inheritDoc
     */
    public function __invoke(array $routeParams): void {

        $fallbackPost = Cms\Post::bySlug($this->cms, $routeParams['slug']);
        if (empty($fallbackPost)) {
            self::notFound();
            return;
        }

        $postGroup = new IntlCmsResource($this->cms, $this->intl, $fallbackPost);

        $this->render('post/post.html.twig',
            $postGroup->getIntl()->title ??
                $postGroup->getFallback()->title,
            ['postGroup' => $postGroup]
        );
    }
}