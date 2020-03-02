<?php

namespace Iwgb\OrgUk\Provider;

use Iwgb\OrgUk\Intl\IntlUtility;
use Pimple\ServiceProviderInterface;
use Pimple\Container;
use Siler\Diactoros as Psr7;
use Siler\Http\Response;

class IntlProvider implements ServiceProviderInterface {

    /**
     * @inheritDoc
     * @noinspection PhpUndefinedVariableInspection
     */
    public function register(Container $c) {

        $c['intl'] = fn(): IntlUtility => new IntlUtility($c['settings']['languages'], Psr7\request(), $c['session'],
            fn(string $uri) => Response\redirect($uri)
        );
    }
}