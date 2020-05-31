<?php

namespace Iwgb\OrgUk\Intl;

use Guym4c\GhostApiPhp\Filter;
use stdClass;
use voku\helper\UTF8;

class IntlUtility {

    private const LANGUAGE_IN_URI_REGEX = "/^\/(?<language>[a-z]{2})\//";

    private ?string $language = null;
    private array $languages;
    private string $fallback;

    public function __construct(array $languages, ?string $fallback = null) {
        $this->languages = $languages;
        $this->fallback = $fallback ?? $languages[0];
    }

    public function getText(string $template, string $key, array $values = []): string {
        if (strpos($template, "/")) {

            $page = (function (string $template): string {
                $templateDirs = explode('/', $template);
                foreach ($templateDirs as $templateDir) {
                    if (substr($templateDir, 0, 1) !== '_') {
                        return $templateDir;
                    }
                }
                return $templateDirs[0];
            })($template);

        } else {
            $page = explode('.', $template)[0];
        }
        return $this->get(UTF8::strtolower($page), $key, $values);
    }

    /**
     * @param string      $page
     * @param string      $key
     * @param array       $values
     * @param string|null $language
     * @return string|string[]
     */
    private function get(string $page, string $key, array $values = [], ?string $language = null) {

        $language = $language ?? $this->language;

        $json = self::read($page, $language);

        // page not found
        if ($json === null) {
            self::write($page, $language, new stdClass());

            if ($language != $this->fallback) {
                return $this->get($page, $key, $values, $this->fallback);
            }
            return '';
        }

        // key does not exist
        if (!isset($json[$key])) {
            $json[$key] = '';
            self::write($page, $language, $json);

            if ($language != $this->fallback) {
                return $this->get($page, $key, $values, $this->fallback);
            }

            return '';
        }

        $value = $json[$key];

        if ($value === ''
            && $language != $this->fallback) {
            return $this->get($page, $key, $values, $this->fallback);
        }

        return preg_replace_callback('/{ *([a-zA-Z0-9\-_.]+) *}/',
            fn(array $matches): string => $values[$matches[1]] ?? $matches[1],
            $value
        );
    }

    public static function readAll(string $language): array {
        $components = [];
        foreach (scandir(APP_ROOT . '/intl') as $component) {
            if (!in_array($component, ['.', '..'])) {
                $components[$component] = self::read($component, $language);
            }
        }
        return $components;
    }

    public static function read(string $page, string $language): ?array {
        $file = self::file($page, $language);
        if (file_exists($file)) {
            return UTF8::json_decode(
                UTF8::file_get_contents(self::file($page, $language)), true);
        }

        return null;
    }

    public static function write(string $page, string $language, $data): void {
        ksort($data, SORT_STRING);
        file_put_contents(self::file($page, $language), UTF8::json_encode($data));
    }

    private static function file(string $page, string $language): string {
        return APP_ROOT . "/intl/{$page}/{$language}.json";
    }

    public static function addToUri(string $language, string $uri): string {
        $uri = self::formatUri($uri);

        if (self::getLanguageFromUri($uri) === $language) {
            return $uri;
        }

        $uri = self::removeFromUri($uri);
        return "/{$language}{$uri}";
    }

    public static function removeFromUri(string $uri): ?string {
        $uri = self::formatUri($uri);
        $language = self::getLanguageFromUri($uri);

        if (empty($language)) {
            return $uri;
        } else {
            if (UTF8::strlen($uri) === 3) {
                return '/';
            } else {
                return str_replace("/{$language}/", '/', $uri);
            }
        }
    }

    /**
     * Retrieve the language from the URI
     * Returns null if no language is in the URI
     *
     * @param string $uri
     * @return string|null
     */
    public static function getLanguageFromUri(string $uri): ?string {
        $uri = self::formatUri($uri);

        if (UTF8::strlen($uri) !== 3) {
            $matches = [];
            preg_match(self::LANGUAGE_IN_URI_REGEX, $uri, $matches);
            return $matches['language'] ?? null;
        } else {
            return UTF8::substr($uri, 1);
        }
    }

    public static function uriHasLanguage(string $uri): bool {
        return self::getLanguageFromUri($uri) !== null;
    }

    private static function formatUri(string $uri): string {
        return UTF8::char_at($uri, 0) !== '/'
            ? "/{$uri}"
            : $uri;
    }

    public function ghostFilterFactory(): Filter {
        return (new Filter())->by('tag', '=', $this->getFallback());
    }

    public function isFallback(): bool {
        return $this->language === $this->fallback;
    }

    /**
     * @return string
     */
    public function getLanguage(): string {
        return $this->language;
    }

    public function setLanguage(string $language): void {
        $this->language = $language;
    }

    /**
     * @return array
     */
    public function getLanguages(): array {
        return $this->languages;
    }

    /**
     * @return string
     */
    public function getFallback(): string {
        return $this->fallback;
    }
}