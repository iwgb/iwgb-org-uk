<?php

namespace Iwgb\OrgUk\Handler\Intl;

use Iwgb\OrgUk\Psr7Utils as Psr7;
use Psr\Http\Message\ResponseInterface;
use Slim\Psr7\Request;
use Slim\Psr7\Response;

class AllLangpacks extends AbstractIntlStoreHandler {

    public function __invoke(Request $request, Response $response, array $args): ResponseInterface {
        $files = $this->intlStore->list();

        $downloadUrls = [];
        foreach ($files as $file) {
            $downloadUrls[] = $this->intlStore->getDownloadUrl($file, true);
        }

        return Psr7::withJson($response, ['files' => $downloadUrls]);
    }
}