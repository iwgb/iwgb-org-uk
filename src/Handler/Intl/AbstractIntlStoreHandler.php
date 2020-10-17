<?php

namespace Iwgb\OrgUk\Handler\Intl;

use Guym4c\PhpS3Intl\CachingS3JsonClient;
use Iwgb\OrgUk\Handler\AbstractIntlHandler;
use Psr\Container\ContainerInterface;

abstract class AbstractIntlStoreHandler extends AbstractIntlHandler {

    protected CachingS3JsonClient $intlStore;

    public function __construct(ContainerInterface $c) {
        parent::__construct($c);

        /** @noinspection PhpFieldAssignmentTypeMismatchInspection */
        $this->intlStore = $this->intl->getStore();
    }
}