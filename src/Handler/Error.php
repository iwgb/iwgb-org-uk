<?php

namespace Iwgb\OrgUk\Handler;

use Exception;

class Error extends RootHandler {

    /**
     * @inheritDoc
     * @throws Exception
     */
    public function __invoke(array $routeParams): void {
        $this->render('error/error.html.twig',
            $this->intl->getText('error', 'title'));
    }
}