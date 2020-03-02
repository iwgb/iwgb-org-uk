<?php

namespace Iwgb\OrgUk\Provider;

use Doctrine\Common\Cache\Cache;
use Doctrine\Common\Cache\FilesystemCache;
use Iwgb\OrgUk\IntlCache;
use Pimple\Container;
use Pimple\ServiceProviderInterface;

class DoctrineCacheProvider implements ServiceProviderInterface {

    /**
     * @inheritDoc
     */
    public function register(Container $c) {
        $c['cache'] = fn (): Cache =>
            new IntlCache($c['intl'], new FilesystemCache(APP_ROOT . '/var/cache/cms'));
    }
}