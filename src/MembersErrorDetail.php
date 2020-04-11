<?php

namespace Iwgb\OrgUk;

use GuzzleHttp;

class MembersErrorDetail {

    private const MEMBERS_ERROR_DETAILS_API_URL = 'https://members.iwgb.org.uk/api/error/';

    public int $code;

    public string $type;

    public string $error;

    public function __construct(GuzzleHttp\Client $http, int $code, array $settings) {
        $responseBody = (string) $http->get(self::MEMBERS_ERROR_DETAILS_API_URL . $code, [
            'headers' => ['Authorization' => "Bearer {$settings['membersApi']['token']}"],
        ])->getBody();

        $details = json_decode($responseBody, true);

        $this->code = $details['code'];
        $this->type = $details['type'];
        $this->error = $details['error'];
    }
}