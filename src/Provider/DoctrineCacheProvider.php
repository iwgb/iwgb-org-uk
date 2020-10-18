<?php

namespace Iwgb\OrgUk\Provider;

use Doctrine\Common\Cache\Cache;
use Doctrine\Common\Cache\FilesystemCache;
use Iwgb\OrgUk\Intl\IntlCache;
use Psr\Container\ContainerInterface;

class DoctrineCacheProvider implements Injectable {

    public function register(): array {
        return [Provider::CACHE => fn (ContainerInterface $c): Cache =>
            new IntlCache($c->get(Provider::INTL), new FilesystemCache(APP_ROOT . '/var/cache/cms'))
        ];
    }
}