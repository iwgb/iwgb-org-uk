<?php

namespace Iwgb\OrgUk\Handler;

use Guym4c\GhostApiPhp\Model as Cms;
use Iwgb\OrgUk\Intl\IntlCmsResource;

class Page extends RootHandler {

    /**
     * @inheritDoc
     */
    public function __invoke(array $routeParams): void {

        $fallbackPage = Cms\Page::bySlug($this->cms, $routeParams['slug']);
        if (empty($fallbackPage)) {
            $this->notFound();
            return;
        }

        $pageGroup = new IntlCmsResource($this->cms, $this->intl, $fallbackPage);

        $this->render('page/page.html.twig',
            $pageGroup->getIntl()->title ??
            $pageGroup->getFallback()->title,
            ['pageGroup' => $pageGroup]
        );
    }
}