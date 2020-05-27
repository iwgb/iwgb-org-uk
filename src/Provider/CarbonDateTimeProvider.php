<?php

namespace Iwgb\OrgUk\Provider;

use Carbon;
use Psr\Container\ContainerInterface;

class CarbonDateTimeProvider implements Injectable {

    public function register(): array {
        return ['datetime' => fn(ContainerInterface $c): Carbon\Factory =>
            new Carbon\Factory(['locale' => $c->get('intl')->getLanguage()])
        ];
    }
}