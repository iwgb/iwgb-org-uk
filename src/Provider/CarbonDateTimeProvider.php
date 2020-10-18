<?php

namespace Iwgb\OrgUk\Provider;

use Carbon;
use Psr\Container\ContainerInterface;

class CarbonDateTimeProvider implements Injectable {

    public function register(): array {
        return [Provider::DATETIME => fn(ContainerInterface $c): Carbon\Factory =>
            new Carbon\Factory(['locale' => $c->get(Provider::INTL)->getLanguage()])
        ];
    }
}