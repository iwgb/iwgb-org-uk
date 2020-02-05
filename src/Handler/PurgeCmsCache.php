<?php

namespace Iwgb\OrgUk\Handler;

use Siler\Http\Response;

class PurgeCmsCache extends RootHandler {

    /**
     * {@inheritDoc}
     */
    public function __invoke(array $routeParams): void {
        $this->cache->purge();
        Response\no_content();
    }
}