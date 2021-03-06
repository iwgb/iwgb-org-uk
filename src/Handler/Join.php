<?php

namespace Iwgb\OrgUk\Handler;

use Guym4c\Airtable\AirtableApiException;
use Guym4c\GhostApiPhp\GhostApiException;
use GuzzleHttp;
use Iwgb\OrgUk\MembersErrorDetail;
use Iwgb\OrgUk\Provider\Provider;
use Psr\Container\ContainerInterface;
use Psr\Http\Message\ResponseInterface;
use Slim\Psr7\Request;
use Slim\Psr7\Response;

class Join extends ViewHandler {

    private GuzzleHttp\Client $http;

    public function __construct(ContainerInterface $c) {
        parent::__construct($c);

        $this->http = $c->get(Provider::HTTP);
    }

    /**
     * @inheritDoc
     * @throws AirtableApiException
     * @throws GhostApiException
     */
    public function __invoke(Request $request, Response $response, array $args): ResponseInterface {

        $data = $request->getQueryParams();

        $jobTypes = $this->membership->list('Job types')->getRecords();
        shuffle($jobTypes);

        foreach ($jobTypes as $i => $jobType) {
            if ($jobType->Slug === 'other') {
                $other = $jobType;
                unset($jobTypes[$i]);
                $jobTypes[] = $other;
                break;
            }
        }

        $code = $data['code'] ?? null;
        $error = null;
        if (!empty($code)) {
            $error = [
                'message' => (new MembersErrorDetail($this->http, $code, $this->settings))->error,
                'aid'     => $data['aid'] ?? '',
            ];
        }

        return $this->render($request, $response,
            'join/join.html.twig',
            $this->intl->getText('join', 'title'),
            [
                'contentGroup' => $this->cms->pageBySlug('join'),
                'jobTypes'     => $jobTypes,
                'meta'         => [
                    'title' => "{$this->intl->getText('join', 'title')} - IWGB",
                    'image' => $this->settings['defaultImage'],
                ],
                'error'        => $error,
            ],
        );
    }
}