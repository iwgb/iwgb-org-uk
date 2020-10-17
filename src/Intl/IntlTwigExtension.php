<?php

namespace Iwgb\OrgUk\Intl;

use Guym4c\PhpS3Intl\IntlController;
use Twig\Extension\AbstractExtension;
use Twig\TwigFunction;
use voku\helper\UTF8;

class IntlTwigExtension extends AbstractExtension {

    private const EXCLUDE_FOLDER_PREFIX = '_';

    private IntlController $intl;

    private bool $addMissingKeys;

    public function __construct(IntlController $intl, bool $addMissingKeys) {
        $this->intl = $intl;
        $this->addMissingKeys = $addMissingKeys;
    }

    private static function directoryIsExcluded(string $directory): bool {
        return UTF8::substr($directory, 0, 1) === self::EXCLUDE_FOLDER_PREFIX;
    }

    public function getFunctions(): array {
        return [
            new TwigFunction(
                '_',
                function (string $page, string $key, array $values = []): string {
                    $templatePath = explode('.', $page)[0];

                    $langpackName = UTF8::str_contains($templatePath, '/')
                        ? (
                            (function (string $template): string {
                                $templateDirectories = explode('/', $template);
                                foreach ($templateDirectories as $directory) {
                                    if (!self::directoryIsExcluded($directory)) {
                                        return $directory;
                                    }
                                }
                                return $templateDirectories[0];
                            })($templatePath)
                        )
                        : explode('.', $templatePath)[0];

                    return $this->intl->getText($langpackName, $key, $values, $this->addMissingKeys);
                }
            )
        ];
    }

}