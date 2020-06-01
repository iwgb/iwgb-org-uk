<?php

namespace Iwgb\OrgUk\Service;

use Guym4c\GhostApiPhp\Filter;
use Guym4c\GhostApiPhp\Ghost;
use Guym4c\GhostApiPhp\GhostApiException;
use Guym4c\GhostApiPhp\Model as Retrieve;
use Guym4c\GhostApiPhp\Sort;
use Guym4c\GhostApiPhp\SortOrder;
use Iwgb\OrgUk\Intl\IntlCache;
use Iwgb\OrgUk\Intl\IntlCmsResource;
use Iwgb\OrgUk\Intl\IntlUtility;

class Cms {

    private Ghost $ghost;

    private IntlUtility $intl;

    private IntlCache $cache;

    /**
     * Cms constructor.
     * @param Ghost $ghost
     * @param IntlUtility $intl
     */
    public function __construct(Ghost $ghost, IntlUtility $intl, IntlCache $cache) {
        $this->ghost = $ghost;
        $this->intl = $intl;
        $this->cache = $cache;
    }

    /**
     * Get a specific page, by slug
     *
     * @param string $slug
     * @return IntlCmsResource
     * @throws GhostApiException
     */
    public function pageBySlug(string $slug): ?IntlCmsResource {
        $fallbackPage = Retrieve\Page::bySlug($this->ghost, $slug);

        if (empty($fallbackPage)) {
            return null;
        }

        return new IntlCmsResource($this->ghost, $this->intl, $fallbackPage);
    }

    /**
     * @param string $slug
     * @return IntlCmsResource|null
     * @throws GhostApiException
     */
    public function postBySlug(string $slug): ?IntlCmsResource {
        $fallbackPost = Retrieve\Post::bySlug($this->ghost, $slug);

        if (empty($fallbackPost)) {
            return null;
        }

        return new IntlCmsResource($this->ghost, $this->intl, $fallbackPost);
    }

    /**
     * @param string $id
     * @param int|null $limit
     * @param Filter|null $filter
     * @param int $page
     * @param Sort|null $sort
     * @return IntlCmsResource[]
     */
    public function listPosts(
        string $id,
        ?int $limit = null,
        ?Filter $filter = null,
        int $page = 1,
        ?Sort $sort = null
    ): array {
        return $this->cache->get($id, fn(): array =>
            $this->listResources(Retrieve\Post::class, $id, $limit, $filter, $page, $sort)
        );
    }

    public function listPages(
        string $id,
        ?int $limit = null,
        ?Filter $filter = null,
        int $page = 1,
        ?Sort $sort = null
    ): array {
        return $this->cache->get($id, fn(): array =>
            $this->listResources(Retrieve\Page::class, $id, $limit, $filter, $page, $sort)
        );
    }

    /**
     * @noinspection PhpUndefinedMethodInspection
     *
     * @param string $resourceClass
     * @param string $id
     * @param int|null $limit
     * @param Filter|null $filter
     * @param int $page
     * @param Sort|null $sort
     * @return Retrieve\AbstractContentResource[]
     */
    private function listResources(
        string $resourceClass,
        string $id,
        ?int $limit = null,
        ?Filter $filter = null,
        int $page = 1,
        ?Sort $sort = null
    ): array {
        return $this->cache->get($id, fn(): array =>
            IntlCmsResource::getIntlResources($this->ghost, $this->intl,
                $resourceClass::get($this->ghost, $limit,
                    $sort ?? new Sort('published_at', SortOrder::DESC),
                    $filter,
                    $page,
                )->getResources(),
            )
        );
    }

    public function flushCache(): void {
        $this->ghost->flushCache();
    }

    public function withLanguage(): Filter {
        return (new Filter())
            ->by('tag', '=', $this->intl->getFallback());
    }

}