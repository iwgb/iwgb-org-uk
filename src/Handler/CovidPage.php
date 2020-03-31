<?php

namespace Iwgb\OrgUk\Handler;

use Guym4c\GhostApiPhp\Model as Cms;
use Iwgb\OrgUk\Intl\IntlCmsResource;

class CovidPage extends RootHandler {

    /**
     * @inheritDoc
     */
    public function __invoke(array $routeParams): void {

        $isLanding = empty($routeParams['page']);

        $fallbackPage = Cms\Page::bySlug($this->cms, $routeParams['page'] ?? 'covid-19');
        if (
            empty($fallbackPage)
            || $fallbackPage->primaryTag->slug !== 'covid-19'
        ) {
            self::notFound();
            return;
        }

        $pageGroup = new IntlCmsResource($this->cms, $this->intl, $fallbackPage);

        $this->render('covid/covid.html.twig',
            $pageGroup->getIntl()->title ??
            $pageGroup->getFallback()->title,
            [
                'pageGroup' => $pageGroup,
                'jason' => $isLanding,
            ]
        );
    }
}