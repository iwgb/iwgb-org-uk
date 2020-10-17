<?php

namespace Iwgb\OrgUk\Handler;

use Guym4c\PhpS3Intl\IntlController;
use Iwgb\OrgUk\Provider\Provider;
use Psr\Container\ContainerInterface;

abstract class AbstractIntlHandler extends AbstractHandler {

    protected IntlController $intl;

    public function __construct(ContainerInterface $c) {
        parent::__construct($c);

        $this->intl = $c->get(Provider::INTL);
    }
}