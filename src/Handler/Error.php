<?php

namespace Iwgb\OrgUk\Handler;

use Exception;
use GuzzleHttp;
use Iwgb\OrgUk\MembersErrorDetail;
use Psr\Container\ContainerInterface;
use Psr\Http\Message\ResponseInterface;
use Slim\Psr7\Request;
use Slim\Psr7\Response;

class Error extends ViewHandler {

    private const DEFAULT_ERROR_CODE = 99;

    private GuzzleHttp\Client $http;

    public function __construct(ContainerInterface $c) {
        parent::__construct($c);

        $this->http = $c->get('http');
    }

    /**
     * @inheritDoc
     * @throws Exception
     */
    public function __invoke(Request $request, Response $response, array $args): ResponseInterface {

        $data = $request->getQueryParams();

        $code = $data['code'] ?? self::DEFAULT_ERROR_CODE;
        $aid = $data['aid'] ?? null;

        $details = [
            'error' => 'Unknown',
            'aid' => $aid,
        ];

        if (!empty($code)) {
             $details['error'] = (new MembersErrorDetail($this->http, $code, $this->settings))->error;
        }

        return $this->render($request, $response,
            'error/error.html.twig',
            $this->intl->getText('error', 'title'),
            $details
        );
    }
}