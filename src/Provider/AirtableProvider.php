<?php

namespace Iwgb\OrgUk\Provider;

use Guym4c\Airtable\Airtable;
use Iwgb\OrgUk\Factory\AirtableClientFactory;
use Psr\Container\ContainerInterface;

class AirtableProvider implements Injectable {

    public function register(): array {
        return [
            Provider::MEMBERSHIPS_AIRTABLE => fn (ContainerInterface $c): Airtable =>
                AirtableClientFactory::build(
                    $c->get(Provider::SETTINGS)['airtable']['key'],
                    $c->get(Provider::SETTINGS)['airtable']['membershipBase'],
                    $c->get(Provider::SETTINGS)['airtable']['proxyKey'],
                    ['Job types'],
                ),
            Provider::BRANCHES_AIRTABLE => fn (ContainerInterface $c): Airtable =>
            AirtableClientFactory::build(
                $c->get(Provider::SETTINGS)['airtable']['key'],
                $c->get(Provider::SETTINGS)['airtable']['branchesBase'],
                $c->get(Provider::SETTINGS)['airtable']['proxyKey'],
                ['Branches'],
            ),
        ];
    }
}