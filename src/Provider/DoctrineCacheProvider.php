<?php

namespace Iwgb\OrgUk\Provider;

use Doctrine\Common\Cache\Cache;
use Doctrine\Common\Cache\FilesystemCache;
use Iwgb\OrgUk\Intl\IntlCache;
use Psr\Container\ContainerInterface;

class DoctrineCacheProvider implements Injectable {

    public function register(): array {
        return ['cache' => fn (ContainerInterface $c): Cache =>
            new IntlCache($c->get('intl'), new FilesystemCache(APP_ROOT . '/var/cache/cms'))
        ];
    }
}