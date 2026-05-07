<?php

namespace Maatwebsite\Excel;

use Maatwebsite\Excel\Cache\CacheManager;
use PhpOffice\PhpSpreadsheet\Settings;

class SettingsProvider
{
    public function __construct(private CacheManager $cache)
    {
    }

    /**
     * Provide PhpSpreadsheet settings.
     */
    public function provide()
    {
        $this->configureCellCaching();
    }

    protected function configureCellCaching()
    {
        Settings::setCache(
            $this->cache->driver()
        );
    }
}
