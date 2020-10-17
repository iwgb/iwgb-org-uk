<?php

namespace Iwgb\OrgUk\Intl;

use Doctrine\Common\Cache\Cache;
use Doctrine\Common\Cache\FlushableCache;
use Guym4c\PhpS3Intl\IntlController;

class IntlCache implements Cache {

    private FlushableCache $cache;

    private IntlController $intl;

    public const NAV_DATA = 'nav-data';
    public const FOOTER_DATA = 'footer-data';
    public const FEATURED_POST = 'featured-post';

    /**
     * IntlCache constructor.
     * @param IntlController    $intl
     * @param FlushableCache $cache
     */
    public function __construct(IntlController $intl, FlushableCache $cache) {
        $this->intl = $intl;
        $this->cache = $cache;
    }


    private function getIntlKey(string $key, ?string $language = null): string {
        $language = $language ?? $this->intl->getLanguage();
        return "{$key}-{$language}";
    }

    /**
     * @inheritDoc
     */
    public function fetch($id) {
        return $this->cache->fetch($this->getIntlKey($id));
    }

    public function get(
        string $id,
        ?callable $retrieve = null,
        ?callable $inCacheTransform = null,
        ?callable $outCacheTransform = null
    ) {
        $data = $this->fetch($id);
        if (
            !empty($data)
            || empty($retrieve)
        ) {
            if ($outCacheTransform !== null) {
                $data = $outCacheTransform($data);
            }
            return $data;
        }

        $data = $retrieve();

        $cacheableData = $inCacheTransform !== null
            ? $inCacheTransform($data)
            : $data;

        $this->save($id, $cacheableData);
        return $data;
    }

    /**
     * @inheritDoc
     */
    public function contains($id) {
        return $this->cache->contains($this->getIntlKey($id));
    }

    /**
     * @inheritDoc
     */
    public function save($id, $data, $lifeTime = 0) {
        return $this->cache->save($this->getIntlKey($id), $data);
    }

    /**
     * @inheritDoc
     */
    public function delete($id) {
        return $this->cache->delete($this->getIntlKey($id));
    }

    /**
     * @inheritDoc
     */
    public function getStats() {
        return $this->cache->getStats();
    }

    public function purge(): void {
        $this->cache->flushAll();
    }
}