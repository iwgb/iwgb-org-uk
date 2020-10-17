<?php

namespace Iwgb\OrgUk\Provider;

use GuzzleHttp;

class GuzzleHttpProvider implements Injectable {

    public function register(): array {
        return [Provider::HTTP => fn (): GuzzleHttp\Client => new GuzzleHttp\Client()];
    }
}