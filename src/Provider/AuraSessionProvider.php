<?php


namespace Iwgb\OrgUk\Provider;


use Aura\Session\Session;
use Aura\Session\SessionFactory;
use Pimple\Container;
use Pimple\ServiceProviderInterface;

class AuraSessionProvider implements ServiceProviderInterface {

    /**
     * @inheritDoc
     */
    public function register(Container $c) {
        session_name('IwgbSession');
        session_set_cookie_params(60 * 60 * 24 * 30 /* 30 days */);
        $c['session'] = fn(): Session => (new SessionFactory())->newInstance($_COOKIE);
    }
}