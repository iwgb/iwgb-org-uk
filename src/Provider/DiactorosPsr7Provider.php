<?php

namespace Iwgb\OrgUk\Provider;

use Pimple\Container;
use Pimple\ServiceProviderInterface;
use Psr\Http\Message\ServerRequestInterface;
use Siler\Diactoros as Psr7;

class DiactorosPsr7Provider implements ServiceProviderInterface {

    /**
     * @inheritDoc
     */
    public function register(Container $c) {
        $c['request'] = fn(): ServerRequestInterface => Psr7\request();
    }
}