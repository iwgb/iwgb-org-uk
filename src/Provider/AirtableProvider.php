<?php

namespace Iwgb\OrgUk\Provider;

use Doctrine\Common\Cache\FilesystemCache;
use Guym4c\Airtable\Airtable;
use Iwgb\OrgUk\Factory\AirtableClientFactory;
use Pimple\Container;
use Pimple\ServiceProviderInterface;

class AirtableProvider implements ServiceProviderInterface {

    /**
     * {@inheritdoc}
     */
    public function register(Container $c) {

        $c['membership'] = fn (Container $c): Airtable =>
            AirtableClientFactory::build(
                $c['settings']['airtable']['key'],
                $c['settings']['airtable']['membershipBase'],
                ['Job types']
            );

        $c['branches'] = fn (Container $c): Airtable =>
            AirtableClientFactory::build(
                $c['settings']['airtable']['key'],
                $c['settings']['airtable']['branchesBase'],
                ['Branches']
            );
    }
}