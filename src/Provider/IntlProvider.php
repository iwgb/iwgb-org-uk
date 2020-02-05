<?php

namespace Iwgb\OrgUk\Provider;

use Iwgb\OrgUk\Intl;
use Pimple\ServiceProviderInterface;
use Pimple\Container;
use Siler\Diactoros as Psr7;
use Siler\Http\Response;

class IntlProvider implements ServiceProviderInterface {

    /**
     * @inheritDoc
     */
    public function register(Container $c) {

        $c['intl'] = fn(): Intl => new Intl($c['settings']['languages'], Psr7\request(), $c['session'],
            fn(string $uri) => Response\redirect($uri)
        );
    }
}