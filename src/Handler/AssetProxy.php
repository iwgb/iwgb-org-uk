<?php

namespace Iwgb\OrgUk\Handler;

use Mimey\MimeTypes;
use Siler\Http\Request;
use Siler\Http\Response;
use Teapot\StatusCode;

class AssetProxy extends RootHandler {

    private const TYPE_TO_DIR_MAP = [
        'icons'  => '/vendor/fortawesome/font-awesome',
        'static' => '/assets',
    ];

    /**
     * {@inheritDoc}
     */
    public function __invoke(array $routeParams): void {
        if (Request\header('X-Pull') === $this->settings['cdn']['assetKey']
            || $this->settings['dev']) {
            self::output(self::TYPE_TO_DIR_MAP[$routeParams['type']], $routeParams['file'], $routeParams['ext']);
        } else {
            Response\output('', StatusCode::FORBIDDEN);
        }
    }


    private static function output(string $path, string $file, string $extension): void {
        $path = APP_ROOT . "{$path}/{$file}.{$extension}";
        if (!file_exists($path)) {
            Response\output('', StatusCode::NOT_FOUND);
        }

        Response\output(
            file_get_contents($path),
            StatusCode::OK,
            (new MimeTypes())->getMimeType($extension) ?? 'application/json'
        );
    }
}