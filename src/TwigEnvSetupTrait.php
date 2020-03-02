<?php

namespace Iwgb\OrgUk;

use Twig;
use Twig\TwigFilter;
use Twig\TwigFunction;

trait TwigEnvSetupTrait {

    /**
     * @param Twig\Environment $twig
     * @param string[]         $globals
     */
    private static function addGlobals(Twig\Environment $twig, array $globals): void {
        foreach ($globals as $key => $value) {
            $twig->addGlobal($key, $value);
        }
    }

    /**
     * @param Twig\Environment $twig
     * @param callable[]       $functions
     */
    private static function addFunctions(Twig\Environment $twig, array $functions) {
        foreach ($functions as $name => $function) {
            $twig->addFunction(new TwigFunction($name, $function));
        }
    }

    private static function addFilters(Twig\Environment $twig, array $filters) {
        foreach ($filters as $name => $filter) {
            $twig->addFilter(new TwigFilter($name, $filter));
        }
    }
}