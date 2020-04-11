<?php

namespace Iwgb\OrgUk\Handler;

use Guym4c\GhostApiPhp\Model as Cms;
use GuzzleHttp;
use Iwgb\OrgUk\Intl\IntlCmsResource;
use Iwgb\OrgUk\MembersErrorDetail;
use Pimple\Container;
use Siler\http\Request;

class Join extends RootHandler {

    private GuzzleHttp\Client $http;

    public function __construct(Container $c) {
        parent::__construct($c);

        $this->http = $c['http'];
    }

    /**
     * @inheritDoc
     */
    public function __invoke(array $routeParams): void {

        $jobTypes = $this->membership->list('Job types')->getRecords();
        shuffle($jobTypes);

        $code = Request\get('code');
        $error = null;
        if (!empty($code)) {
            $error = [
                'message' => (new MembersErrorDetail($this->http, $code, $this->settings))->error,
                'aid'     => Request\get('aid'),
            ];
        }

        $this->render('join/join.html.twig', $this->intl->getText('join', 'title'), [
            'contentGroup' => new IntlCmsResource($this->cms, $this->intl, Cms\Page::bySlug($this->cms, 'join')),
            'jobTypes'     => $jobTypes,
            'meta'         => [
                'title' => "IWGB: {$this->intl->getText('join', 'title')}",
                'image' => $this->settings['defaultImage'],
            ],
            'error'        => $error,
        ]);
    }
}