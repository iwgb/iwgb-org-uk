<?php

namespace Iwgb\OrgUk\Handler;

use Mimey\MimeTypes;
use Psr\Http\Message\ResponseInterface;
use Slim\Psr7;
use Slim\Psr7\Request;
use Slim\Psr7\Response;
use Teapot\StatusCode;

class AssetProxy extends AbstractHandler {

    private const TYPE_TO_DIR_MAP = [
        'icons'  => '/vendor/fortawesome/font-awesome',
        'static' => '/assets',
    ];

    /**
     * {@inheritDoc}
     */
    public function __invoke(Request $request, Response $response, array $args): ResponseInterface {
        if (
            $request->getHeaderLine('X-Pull') === $this->settings['cdn']['assetKey']
            || !$this->settings['is_prod']
        ) {
            return self::output(
                $response,
                self::TYPE_TO_DIR_MAP[$args['type'] ?? ''] ?? '',
                $args['file'] ?? '',
                $args['ext'] ?? '',
            );
        } else {
            return $response->withStatus(StatusCode::FORBIDDEN);
        }
    }


    private static function output(Response $response, string $path, string $file, string $extension): ResponseInterface {
        $path = APP_ROOT . "{$path}/{$file}.{$extension}";

        if (!file_exists($path)) {
            return $response->withStatus(StatusCode::NOT_FOUND);
        }

        /** @noinspection PhpIncompatibleReturnTypeInspection */
        return $response
            ->withHeader('Content-Type', (new MimeTypes())->getMimeType($extension) ?? 'application/json')
            ->withHeader('Content-Length', (string) filesize($path))
            ->withBody(new Psr7\Stream(fopen($path, 'r')));
    }
}