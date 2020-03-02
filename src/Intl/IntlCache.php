<?php

namespace Iwgb\OrgUk\Intl;

use Doctrine\Common\Cache\Cache;
use ReflectionClass;

class IntlCache implements Cache {

    private Cache $cache;

    private IntlUtility $intl;

    public const NAV_DATA = 'nav-data';
    public const FOOTER_DATA = 'footer-data';
    public const FEATURED_POST = 'featured-post';

    /**
     * IntlCache constructor.
     * @param IntlUtility $intl
     * @param Cache       $cache
     */
    public function __construct(IntlUtility $intl, Cache $cache) {
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

    public function get(string $id, callable $retrieve) {
        $data = $this->fetch($id);
        if (!empty($data)) {
            return $data;
        }

        $data = $retrieve();

        $this->save($id, $data);
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
        foreach ((new ReflectionClass(self::class))->getConstants() as $key) {
            foreach ($this->intl->getLanguages() as $language) {
                $this->cache->delete($this->getIntlKey($key, $language));
            }
        }
    }
}