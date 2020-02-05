<?php


namespace Iwgb\OrgUk\Provider;

use Pimple\ServiceProviderInterface;
use Pimple\Container;
use Siler\Twig as Template;
use Twig;

class TwigTemplateProvider implements ServiceProviderInterface {

    /**
     * @inheritDoc
     */
    public function register(Container $c) {
        $c['view'] = fn (): Twig\Environment => Template\init(
            APP_ROOT . '/view',
            $c['settings']['dev'] ? false : APP_ROOT . '/var/twig',
            $c['settings']['dev']
        );
    }
}