<?php

namespace Iwgb\OrgUk\Provider;

use Iwgb\OrgUk\Intl\IntlUtility;
use Psr\Container\ContainerInterface;

class IntlProvider implements Injectable {

    public function register(): array {
        return ['intl' => fn(ContainerInterface $c): IntlUtility =>
            new IntlUtility($c->get('settings')['languages'])
        ];
    }
}