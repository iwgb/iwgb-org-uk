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
                    $c->get('settings')['airtable']['key'],
                    $c->get('settings')['airtable']['membershipBase'],
                    $c->get('settings')['airtable']['proxyKey'],
                    ['Job types'],
                ),
            Provider::BRANCHES_AIRTABLE => fn (ContainerInterface $c): Airtable =>
            AirtableClientFactory::build(
                $c->get('settings')['airtable']['key'],
                $c->get('settings')['airtable']['branchesBase'],
                $c->get('settings')['airtable']['proxyKey'],
                ['Branches'],
            ),
        ];
    }
}