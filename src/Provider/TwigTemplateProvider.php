<?php


namespace Iwgb\OrgUk\Provider;

use Psr\Container\ContainerInterface;
use Twig;
use Twig\Loader\FilesystemLoader;

class TwigTemplateProvider implements Injectable {

    public function register(): array {
        return [
            Provider::TWIG => fn (ContainerInterface $c): Twig\Environment => new Twig\Environment(
                new FilesystemLoader(APP_ROOT . '/view'),
                [
                    'cache' => $c->get(Provider::SETTINGS)['dev']
                        ? false
                        : APP_ROOT . '/var/twig',
                    'debug' => $c->get(Provider::SETTINGS)['dev'],
                ],
            ),
        ];
    }
}