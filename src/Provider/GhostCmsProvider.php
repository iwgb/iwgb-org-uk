<?php

namespace Iwgb\OrgUk\Provider;

use Doctrine\Common\Cache\FilesystemCache;
use Guym4c\GhostApiPhp\Ghost;
use Psr\Container\ContainerInterface;

class GhostCmsProvider implements Injectable {

    public function register(): array {

        return [Provider::CMS => function (ContainerInterface $c): Ghost {
            $ghost = $c->get(Provider::SETTINGS)['cms'];
            return new Ghost(
                $ghost['baseUrl'],
                $ghost['key'],
                new FilesystemCache(APP_ROOT . '/var/cache/ghost')
            );
        }];
    }
}