<?php

namespace Iwgb\OrgUk\Provider;

use Carbon;
use Pimple\Container;
use Pimple\ServiceProviderInterface;

class CarbonDateTimeProvider implements ServiceProviderInterface {

    /**
     * @inheritDoc
     */
    public function register(Container $c) {
        $c['datetime'] = fn(): Carbon\Factory => new Carbon\Factory(['locale' => $c['intl']->getLanguage()]);
    }
}