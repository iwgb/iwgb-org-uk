<?php

$keys = require APP_ROOT . '/keys.php';

return [
    'dev'       => true,
    'db'        => [
        'dbname'   => '',
        'user'     => '',
        'password' => '',
    ],
    'cdn'       => [
        'baseUrl'      => 'https://cdn.iwgb.org.uk',
        'assetKey'     => $keys['assetCdn'],
        'assetBaseUrl' => 'https://iwgb-assets-f208.kxcdn.com',
    ],
    'languages' => ['en', 'es', 'pt'],
    'cms'       => [
        'baseUrl' => 'https://cms.iwgb.org.uk',
        'key'     => $keys['ghost'],
    ],
    'recaptcha' => [
        'siteKey' => '6Lf-ps0UAAAAAEmNpP9nWUeR2MaAfn7FjX9U3s_X',
        'secret'  => $keys['recaptcha'],
    ],
    'airtable'  => [
        'key'  => $keys['airtable'],
        'base' => 'app8RK2AsBtnIcezs',
    ],
];