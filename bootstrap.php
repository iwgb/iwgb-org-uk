<?php

use Iwgb\OrgUk\Provider;

define('APP_ROOT', __DIR__);

require APP_ROOT . '/vendor/autoload.php';

return (new \Pimple\Container([
    'settings' => require APP_ROOT . '/settings.php',
    'time' => [
        'app-init' => microtime(true),
    ],
]))->register(new Provider\TwigTemplateProvider())
    ->register(new Provider\IntlProvider())
    ->register(new Provider\GhostCmsProvider())
    ->register(new Provider\AirtableProvider())
    ->register(new Provider\AuraSessionProvider())
    ->register(new Provider\DoctrineCacheProvider())
    ->register(new Provider\CarbonDateTimeProvider())
    ->register(new Provider\DiactorosPsr7Provider())
    ->register(new Provider\GuzzleHttpProvider());
