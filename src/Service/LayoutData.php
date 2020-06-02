<?php

namespace Iwgb\OrgUk\Service;

use Guym4c\Airtable\Airtable;
use Iwgb\OrgUk\Intl\IntlCache;
use Iwgb\OrgUk\Intl\IntlUtility;

class LayoutData {

    private Cms $cms;

    private Airtable $branches;

    private IntlCache $cache;

    private IntlUtility $intl;

    /**
     * LayoutData constructor.
     * @param Cms $cms
     * @param Airtable $branches
     * @param IntlCache $cache
     * @param IntlUtility $intl
     */
    public function __construct(Cms $cms, Airtable $branches, IntlCache $cache, IntlUtility $intl) {
        $this->cms = $cms;
        $this->branches = $branches;
        $this->cache = $cache;
        $this->intl = $intl;
    }

    /**
     * @return array
     */
    public function nav(): array {
        return $this->cache->get('nav', function (): array {
            $branches = $this->branches->list('Branches')->getRecords();
            shuffle($branches);

            return ['nav' => [
                'Donate'    => [
                    'kind'   => 'internal',
                    'href'   => '/page/donate',
                    'mdHide' => true,
                ],
                'News'      => [
                    'kind' => 'menu',
                    'id'   => 'news',
                    'data' => $this->cms->listPosts(
                        'nav-news',
                        2,
                        $this->cms->withLanguage(),
                    ),
                ],
                'Campaigns' => [
                    'kind'   => 'menu',
                    'id'     => 'campaigns',
                    'mdHide' => true,
                    'data' => $this->cms->listPages(
                        'nav-campaigns',
                        null,
                        $this->cms->withLanguage()
                            ->by('tag', '=', $this->intl->getLanguage())
                            ->and('tag', '=', 'category-campaign')
                    ),
                ],
                'Branches'  => [
                    'kind' => 'menu',
                    'id'   => 'branches',
                    'data' => $branches,
                ],
                'Resources' => [
                    'kind' => 'menu',
                    'id'   => 'resources',
                    'data' =>  Cms::groupBySubcategory(
                        $this->cms->pagesByTag('category-resources')
                    ),
                ],
                'About'     => [
                    'kind' => 'menu',
                    'id'   => 'about',
                    'data' => Cms::groupBySubcategory(
                        $this->cms->pagesByTag('category-about')
                    ),
                ],
            ]];
        });
    }

    /**
     * @return array
     */
    public function footer(): array {
        return $this->cache->get('footer', fn(): array => ['footer' => [
            'about'     => $this->cms->pagesByTag('subcategory-about'),
            'resources' => $this->cms->pagesByTag('subcategory-resources'),
            'legal'     => $this->cms->pagesByTag('subcategory-legal'),
        ]]);
    }

}