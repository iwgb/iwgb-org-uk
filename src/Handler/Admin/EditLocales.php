<?php

namespace Iwgb\OrgUk\Handler\Admin;

use Iwgb\OrgUk\Handler\RootHandler;
use Iwgb\OrgUk\Intl\IntlUtility;

class EditLocales extends RootHandler {

    /**
     * {@inheritDoc}
     */
    public function __invoke(array $routeParams): void {

        $languages = [];
        foreach ($this->intl->getLanguages() as $language) {
            $languages[$language] = IntlUtility::readAll($language);
        }

        $this->render('admin/admin-root.html.twig', 'Edit locales', ['data' => $languages]);
    }
}