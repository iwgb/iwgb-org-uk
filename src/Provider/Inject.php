<?php

namespace Iwgb\OrgUk\Provider;

class Inject {

    /**
     * @param array $providers
     * @return Injectable[]
     */
    public static function providers(array $providers): array {
        $definitions = [];
        foreach ($providers as $provider) {
            foreach ($provider->register() as $key => $service) {
                $definitions[$key] = $service;
            }
        }
        return $definitions;
    }
}