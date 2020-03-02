<?php

namespace Maatwebsite\Excel\Config;

use Maatwebsite\Excel\Cache\CacheManager;
use PhpOffice\PhpSpreadsheet\Cell\Cell;
use PhpOffice\PhpSpreadsheet\Settings;

class SettingsProvider
{
    /**
     * @var CacheManager
     */
    private $cache;

    /**
     * @param CacheManager $cache
     */
    public function __construct(CacheManager $cache)
    {
        $this->cache = $cache;
    }

    /**
     * Provide PhpSpreadsheet settings.
     */
    public function provide()
    {
        $this->provideCache();
        $this->provideValueBinder();
    }

    /**
     * Configure PhpSpreadsheet cell caching.
     */
    protected function provideCache()
    {
        Settings::setCache(
            $this->cache->driver()
        );
    }

    /**
     * Provide the default value binder.
     */
    protected function provideValueBinder()
    {
        Cell::setValueBinder(
            Configuration::getDefaultValueBinder()
        );
    }
}
