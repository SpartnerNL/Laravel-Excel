<?php

namespace Maatwebsite\Excel\Config;

use PhpOffice\PhpSpreadsheet\Settings;
use PhpOffice\PhpSpreadsheet\Cell\Cell;
use Maatwebsite\Excel\Cache\CacheManager;

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
