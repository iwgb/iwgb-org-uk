<?php

namespace Iwgb\OrgUk\Provider;

use Aura\Session\Session;
use Aura\Session\SessionFactory;

class AuraSessionProvider implements Injectable {

    private const COOKIE_LIFETIME = 2592000; // 30 days

    public function register(): array {
        session_name('IwgbSession');
        session_set_cookie_params(self::COOKIE_LIFETIME);
        return [Provider::SESSION => fn(): Session => (new SessionFactory())->newInstance($_COOKIE)];
    }
}