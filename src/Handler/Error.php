<?php

namespace Iwgb\OrgUk\Handler;

use Exception;
use GuzzleHttp;
use Iwgb\OrgUk\MembersErrorDetail;
use Pimple\Container;
use Siler\http\Request;

class Error extends RootHandler {

    private GuzzleHttp\Client $http;

    public function __construct(Container $c) {
        parent::__construct($c);

        $this->http = $c['http'];
    }

    /**
     * @inheritDoc
     * @throws Exception
     */
    public function __invoke(array $routeParams): void {

        $code = Request\get('code');
        $aid = Request\get('aid');

        $details = [
            'error' => 'Unknown',
            'aid' => $aid ?? null,
        ];

        if (!empty($code)) {
             $details['error'] = (new MembersErrorDetail($this->http, $code, $this->settings))->error;
        }

        $this->render('error/error.html.twig', $this->intl->getText('error', 'title'), $details);
    }
}