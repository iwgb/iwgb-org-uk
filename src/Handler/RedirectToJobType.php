<?php

namespace Iwgb\OrgUk\Handler;

class RedirectToJobType extends RootHandler {

    private const ONLINE_JOINING_BASE_ENTRY_POINT = 'https://members.iwgb.org.uk/join/';

    /**
     * @inheritDoc
     */
    public function __invoke(array $routeParams): void {
        $this->render(
            'redirect/redirect.html.twig',
            $this->intl->getText('join', 'title'),
            [
                'url' => self::ONLINE_JOINING_BASE_ENTRY_POINT . $routeParams['jobType'],
                'meta'         => [
                    'title' => "{$this->intl->getText('join', 'title')} - IWGB",
                    'image' => $this->settings['defaultImage'],
                ],
            ],
        );
    }
}