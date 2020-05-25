<?php

use Iwgb\OrgUk\Provider;
use Iwgb\OrgUk\Provider\Inject;

define('APP_ROOT', __DIR__);

require APP_ROOT . '/vendor/autoload.php';

return (new DI\ContainerBuilder())
    ->useAutowiring(false)
    ->addDefinitions(array_merge(
        require APP_ROOT . '/src/settings.php',
        Inject::providers([
            new Provider\TwigTemplateProvider(),
            new Provider\GhostCmsProvider(),
            new Provider\AirtableProvider(),
            new Provider\AuraSessionProvider(),
            new Provider\DoctrineCacheProvider(),
            new Provider\CarbonDateTimeProvider(),
            new Provider\GuzzleHttpProvider(),
            new Provider\IntlProvider(),
        ])
    ))
    ->build();
