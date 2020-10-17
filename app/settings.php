<?php

return ['settings' => [
    'dev' => $_ENV['ENVIRONMENT'] === 'dev',
    'is_prod' => !in_array($_ENV['ENVIRONMENT'], ['dev', 'qa']),
    'environment' => $_ENV['ENVIRONMENT'],
    'cdn' => [
        'baseUrl' => $_ENV['CDN_BASE_URL'],
        'assetKey' => $_ENV['ASSET_CDN_KEY'],
        'assetBaseUrl' => $_ENV['ASSET_CDN_BASE_URL'],
    ],
    'languages' => explode(',', $_ENV['AVAILABLE_LANGUAGES']),
    'intlApiKey' => $_ENV['INTL_API_KEY'],
    'cms' => [
        'baseUrl' => $_ENV['GHOST_BASE_URL'],
        'key' => $_ENV['GHOST_API_KEY'],
        'purgeKey' => $_ENV['ASSET_CDN_KEY'],
    ],
    'recaptcha' => [
        'siteKey' => $_ENV['RECAPTCHA_SITE_KEY'],
        'secret' => $_ENV['RECAPTCHA_SECRET'],
    ],
    'airtable' => [
        'key' => $_ENV['AIRTABLE_API_KEY'],
        'membershipBase' => $_ENV['AIRTABLE_MEMBERSHIP_BASE'],
        'branchesBase' => $_ENV['AIRTABLE_BRANCHES_BASE'],
        'proxyKey' => $_ENV['AIRTABLE_PROXY_KEY'],
    ],
    'mailgun' => [
        'key' => $_ENV['MAILGUN_API_KEY'],
        'domain' => $_ENV['MAILGUN_DOMAIN'],
        'from' => $_ENV['MAILGUN_FROM_ADDR']
    ],
    'contacts' => [
        'enquiries' => $_ENV['CONTACT_ENQUIRIES'],
        'memberships' => $_ENV['CONTACT_MEMBERSHIPS'],
        'dataProtection' => $_ENV['CONTACT_DATAPROTECTION'],
        'press' => $_ENV['CONTACT_PRESS'],
    ],
    'defaultImage' => $_ENV['DEFAULT_SOCIAL_IMAGE'],
    'membersApi' => [
        'token' => $_ENV['IWGB_MEMBERS_API_KEY'],
    ],
    'sentry' => [
        'dsn' => $_ENV['SENTRY_DSN'],
    ],
    's3' => [
        'key' => $_ENV['S3_KEY'],
        'secret' => $_ENV['S3_SECRET'],
        'region' => $_ENV['S3_REGION'],
        'endpoint' => $_ENV['S3_ENDPOINT'],
        'bucket' => $_ENV['S3_BUCKET'],
        'cdnUrl' => $_ENV['S3_CDN_URL'],
    ],
]];