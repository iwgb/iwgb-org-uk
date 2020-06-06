<?php

namespace Iwgb\OrgUk\Intl;

use Guym4c\GhostApiPhp\Ghost;
use Guym4c\GhostApiPhp\GhostApiException;
use Guym4c\GhostApiPhp\Model as Cms;
use InvalidArgumentException;
use voku\helper\UTF8;

class CmsResource {

    private Ghost $cms;

    private IntlUtility $intl;

    private string $type;

    private ?Cms\AbstractContentResource $intlResource = null;

    private ?Cms\AbstractContentResource $fallbackResource = null;

    private array $legacyResource;

    /**
     * IntlCmsResource constructor.
     * @param Ghost       $cms
     * @param IntlUtility $intl
     * @param       $fallbackResource
     * @throws GhostApiException
     */
    public static function construct(Ghost $cms, IntlUtility $intl, Cms\AbstractContentResource $fallbackResource): self {
        $resource = new self();
        $resource->cms = $cms;
        $resource->intl = $intl;
        $resource->fallbackResource = $fallbackResource;

        if ($fallbackResource instanceof Cms\Page) {
            $resource->type = 'page';
            $resource->intlResource = $resource->getIntlPage($fallbackResource);
        } elseif ($fallbackResource instanceof Cms\Post) {
            $resource->type = 'post';
            $resource->intlResource = $resource->getIntlPost($fallbackResource);
        } else {
            throw new InvalidArgumentException("Invalid resource type provided");
        }

        return $resource;
    }

    public static function fromLegacyResource(Ghost $cms, IntlUtility $intl, array $legacyResource): self {
        $resource = new self();
        $resource->cms = $cms;
        $resource->intl = $intl;
        $resource->type = 'post';
        $resource->legacyResource = $legacyResource;

        return $resource;
    }

    /**
     * @param Cms\Page $page
     * @return Cms\Page|null
     * @throws GhostApiException
     */
    private function getIntlPage(Cms\Page $page): ?Cms\Page {
        if (!$this->intl->isFallback()) {
            $page = self::getIntlResource(fn(string $slug): ?Cms\Page =>
                Cms\Page::bySlug($this->cms, $slug), $this->intl, $page);
            /** @var Cms\Page|null $page */
            return $page;
        }
        return null;
    }

    /**
     * @param Cms\Post $post
     * @return Cms\Post|null
     * @throws GhostApiException
     */
    private function getIntlPost(Cms\Post $post): ?Cms\Post {
        if (!$this->intl->isFallback()) {
            $post = self::getIntlResource(fn(string $slug): ?Cms\Post =>
                Cms\Post::bySlug($this->cms, $slug), $this->intl, $post);
            /** @var Cms\Post|null $post */
            return $post;
        }
        return null;
    }

    /**
     * @param callable                    $getResource
     * @param IntlUtility                 $intl
     * @param Cms\AbstractContentResource $resource
     * @return Cms\AbstractContentResource|null
     * @throws GhostApiException
     */
    private static function getIntlResource(
        callable $getResource,
        IntlUtility $intl,
        Cms\AbstractContentResource $resource
    ): ?Cms\AbstractContentResource {
        return self::bySlug($getResource, "{$resource->slug}-{$intl->getLanguage()}");
    }

    /**
     * @param callable $getResource
     * @param string $slug
     * @return Cms\AbstractContentResource|null
     * @throws GhostApiException
     */
    public static function bySlug(callable $getResource, string $slug): ?Cms\AbstractContentResource {
        try {
            $resource = $getResource($slug);
        } catch (GhostApiException $e) {
            if (UTF8::str_contains($e->getMessage(), 'NotFoundError')) {
                $resource = null;
            } else {
                throw $e;
            }
        }
        return $resource;
    }

    /**
     * @param Ghost       $cms
     * @param IntlUtility $intl
     * @param array       $resources
     * @return array
     * @throws GhostApiException
     */
    public static function getIntlResources(Ghost $cms, IntlUtility $intl, array $resources): array {
        $intlResources = [];
        foreach ($resources as $resource) {
            $intlResources[] = self::construct($cms, $intl, $resource);
        }
        return $intlResources;
    }

    /**
     * @return Cms\AbstractContentResource|null
     */
    public function getIntl() {
        return $this->intlResource;
    }

    /**
     * @return Cms\AbstractContentResource|array|null
     */
    public function getFallback() {
        return$this->fallbackResource ?? $this->legacyResource;
    }
}