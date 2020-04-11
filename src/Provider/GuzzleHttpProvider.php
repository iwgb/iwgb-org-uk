<?php

namespace Iwgb\OrgUk\Provider;

use GuzzleHttp;
use Pimple\Container;
use Pimple\ServiceProviderInterface;

class GuzzleHttpProvider implements ServiceProviderInterface {

    /**
     * {@inheritdoc}
     */
    public function register(Container $c) {
        $c['http'] = fn (): GuzzleHttp\Client => new GuzzleHttp\Client();
    }
}