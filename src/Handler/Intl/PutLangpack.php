<?php

namespace Iwgb\OrgUk\Handler\Intl;

use Guym4c\PhpS3Intl\IntlController;
use Iwgb\OrgUk\Psr7Utils as Psr7;
use Psr\Http\Message\ResponseInterface;
use Slim\Psr7\Request;
use Slim\Psr7\Response;

class PutLangpack extends AbstractIntlStoreHandler {

    public function __invoke(Request $request, Response $response, array $args): ResponseInterface {
        $langpackName = $args['langpack'] ?? '';
        $language = $args['language'] ?? '';

        $uploadUrl = $this->intlStore
            ->getUploadUrl(IntlController::getLangpackFileKey($langpackName, $language));

        return Psr7::withJson($response, ['uploadUrl' => $uploadUrl]);
    }
}