<?php

namespace Iwgb\OrgUk\Handler\Intl;

use Guym4c\PhpS3Intl\IntlController;
use GuzzleHttp\Client as HttpClient;
use Iwgb\OrgUk\Provider\Provider;
use Iwgb\OrgUk\Psr7Utils as Psr7;
use Psr\Container\ContainerInterface;
use Psr\Http\Message\ResponseInterface;
use Slim\Psr7\Request;
use Slim\Psr7\Response;

class PutLangpack extends AbstractIntlStoreHandler {

    private HttpClient $http;

    public function __construct(ContainerInterface $c) {
        parent::__construct($c);

        $this->http = $c->get(Provider::HTTP);
    }

    public function __invoke(Request $request, Response $response, array $args): ResponseInterface {
        $langpackName = $args['langpack'] ?? '';
        $language = $args['language'] ?? '';

        $uploadUrl = $this->intlStore
            ->getUploadUrl(IntlController::getLangpackFileKey($langpackName, $language));

        $this->http->get("https://api.keycdn.com/zones/purge/{$this->settings['cdn']['zoneId']}.json", [
            'auth' => [$this->settings['cdn']['apiKey'], ''],
        ]);

        return Psr7::withJson($response, ['uploadUrl' => $uploadUrl]);
    }
}