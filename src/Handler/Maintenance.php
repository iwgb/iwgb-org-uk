<?php

namespace Iwgb\OrgUk\Handler;

use Exception;

class Maintenance extends RootHandler {

    /**
     * @inheritDoc
     * @throws Exception
     */
    public function __invoke(array $routeParams): void {
        $this->render('maintenance.html.twig',
            $this->intl->getText('maintenance', 'title'));
    }
}