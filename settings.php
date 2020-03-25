<?php

$keys = require APP_ROOT . '/keys.php';

return [
    'dev'          => true,
    'db'           => [
        'dbname'   => '',
        'user'     => '',
        'password' => '',
    ],
    'cdn'          => [
        'baseUrl'      => 'https://cdn.iwgb.org.uk',
        'assetKey'     => $keys['assetCdn'],
        'assetBaseUrl' => 'https://iwgbassets-f208.kxcdn.com',
    ],
    'languages'    => ['en', 'es'],
    'cms'          => [
        'baseUrl' => 'https://cms.iwgb.org.uk',
        'key'     => $keys['ghost'],
    ],
    'recaptcha'    => [
        'siteKey' => '6Lf-ps0UAAAAAEmNpP9nWUeR2MaAfn7FjX9U3s_X',
        'secret'  => $keys['recaptcha'],
    ],
    'airtable'     => [
        'key'  => $keys['airtable'],
        'base' => 'app8RK2AsBtnIcezs',
    ],
    'mailgun'      => [
        'key'    => $keys['mailgun'],
        'domain' => 'mx.iwgb.org.uk',
        'from'   => 'activist-robot-noreply@iwgb.org.uk'
    ],
    'contacts'     => [
        'enquiries'      => 'office@iwgb.org.uk',
        'memberships'    => 'memberships@iwgb.co.uk',
        'dataProtection' => 'dataprotection@iwgb.co.uk',
        'press'          => 'press@iwgb.co.uk',
    ],
    'defaultImage' => 'https://cdn.iwgb.org.uk/bucket/home/header5.jpg',
];