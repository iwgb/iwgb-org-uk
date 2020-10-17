<?php

namespace Iwgb\OrgUk\Handler\Intl;

use Iwgb\OrgUk\Psr7Utils as Psr7;
use Psr\Http\Message\ResponseInterface;
use Slim\Psr7\Request;
use Slim\Psr7\Response;

class GetLangpack extends AbstractIntlStoreHandler {

    public function __invoke(Request $request, Response $response, array $args): ResponseInterface {
        $langpackName = $args['langpack'] ?? '';
        $language = $args['language'] ?? '';

        $langpack = $this->intl->getLangpack($langpackName, $language, true);

        return Psr7::withJson($response, $langpack);
    }
}