<?php

namespace Iwgb\OrgUk\Provider;

use Guym4c\GhostApiPhp\Ghost;
use Pimple\ServiceProviderInterface;
use Pimple\Container;

class GhostCmsProvider implements ServiceProviderInterface {

    /**
     * @inheritDoc
     */
    public function register(Container $c) {
        $ghost = $c['settings']['cms'];
        $c['cms'] = fn (): Ghost => new Ghost($ghost['baseUrl'], $ghost['key']);
    }
}