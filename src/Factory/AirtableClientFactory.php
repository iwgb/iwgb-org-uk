<?php

namespace Iwgb\OrgUk\Factory;

use Doctrine\Common\Cache\FilesystemCache;
use Guym4c\Airtable\Airtable;
use Pimple\Container;
use Pimple\ServiceProviderInterface;

class AirtableClientFactory {

    /**
     * {@inheritdoc}
     */
    public static function build(string $key, string $baseId, array $cachableTables = [], string $proxyKey) {

        return new Airtable($key, $baseId,
            new FilesystemCache(APP_ROOT . "/var/cache/airtable/{$baseId}"),
            $cachableTables,
            'https://airtable.iwgb.org.uk/v0',
            ['X-Proxy-Auth' => $proxyKey],
            false
        );
    }
}