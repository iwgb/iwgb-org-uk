<?php

namespace Iwgb\OrgUk\Provider;

use Aura\Session\Session;
use Aura\Session\SessionFactory;

class AuraSessionProvider implements Injectable {

    public function register(): array {
        session_name('IwgbSession');
        session_set_cookie_params(60 * 60 * 24 * 30 /* 30 days */);
        return ['session' => fn(): Session => (new SessionFactory())->newInstance($_COOKIE)];
    }
}