<?php

namespace Iwgb\OrgUk\Handler;

use Guym4c\GhostApiPhp\Model as Cms;
use Iwgb\OrgUk\Intl\IntlCmsResource;

class Join extends RootHandler {

    /**
     * @inheritDoc
     */
    public function __invoke(array $routeParams): void {

        $jobTypes = $this->membership->list('Job types')->getRecords();
        shuffle($jobTypes);

        $this->render('join/join.html.twig', $this->intl->getText('join', 'title'), [
            'contentGroup' => new IntlCmsResource($this->cms, $this->intl, Cms\Page::bySlug($this->cms, 'join')),
            'jobTypes'     => $jobTypes,
            'meta' => [
                'title' => "IWGB: {$this->intl->getText('join', 'title')}",
                'image' => $this->settings['defaultImage'],
            ],
        ]);
    }
}