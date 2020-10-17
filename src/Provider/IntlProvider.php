<?php

namespace Iwgb\OrgUk\Provider;

use Aws\S3\S3Client;
use Doctrine\Common\Cache\FilesystemCache;
use Guym4c\PhpS3Intl\CachingS3JsonClient;
use Guym4c\PhpS3Intl\IntlController;
use Psr\Container\ContainerInterface;

class IntlProvider implements Injectable {

    public function register(): array {
        return [Provider::INTL => fn(ContainerInterface $c): IntlController =>
            new IntlController(
                new CachingS3JsonClient(
                    new S3Client([
                        'version'    => 'latest',
                        'region'     => $c->get('settings')['s3']['region'],
                        'endpoint'   => $c->get('settings')['s3']['endpoint'],
                        'credentials'=> [
                            'key' => $c->get('settings')['s3']['key'],
                            'secret' => $c->get('settings')['s3']['secret'],
                        ],
                    ]),
                    $c->get('settings')['s3']['bucket'],
                    new FilesystemCache(APP_ROOT . '/var/cache/intl'),
                    'intl/',
                    $c->get('settings')['s3']['cdnUrl'],
                ),
                $c->get('settings')['languages'],
            )
        ];
    }
}