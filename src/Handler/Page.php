<?php

namespace Iwgb\OrgUk\Handler;

use Guym4c\GhostApiPhp\Model as Cms;

class Page extends RootHandler {

    /**
     * @inheritDoc
     */
    public function __invoke(array $routeParams): void {

        /** @var Cms\Post[] $postGroup */
        $pageGroup = self::populatePageGroup($this->cms, $this->intl,
            Cms\Page::bySlug($this->cms, $routeParams['slug'])
        );

        if (empty($pageGroup[$this->intl->getFallback()])) {
            $this->notFound();
            return;
        }

        $this->render('page/page.html.twig',
            $pageGroup[$this->intl->getLanguage()]->title ??
            $pageGroup[$this->intl->getFallback()]->title,
            ['pageGroup' => $pageGroup]
        );
    }
}