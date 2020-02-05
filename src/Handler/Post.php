<?php

namespace Iwgb\OrgUk\Handler;

use Guym4c\GhostApiPhp\Model as Cms;

class Post extends RootHandler {

    /**
     * @inheritDoc
     */
    public function __invoke(array $routeParams): void {

        /** @var Cms\Post[] $postGroup */
        $postGroup = self::populatePostGroup($this->cms, $this->intl,
            Cms\Post::bySlug($this->cms, $routeParams['slug'])
        );

        if (empty($postGroup[$this->intl->getFallback()])) {
            $this->notFound();
            return;
        }

        $this->render('post/post.html.twig',
            $postGroup[$this->intl->getLanguage()]->title ??
                $postGroup[$this->intl->getFallback()]->title,
            ['postGroup' => $postGroup]
        );
    }
}