<?php

namespace Maatwebsite\Excel;

use Maatwebsite\Excel\Cache\CacheManager;
use PhpOffice\PhpSpreadsheet\Settings;

class SettingsProvider
{
    public function __construct(
        private readonly CacheManager $cache,
    ) {
    }

    /**
     * Provide PhpSpreadsheet settings.
     */
    public function provide(): void
    {
        $this->configureCellCaching();
    }

    protected function configureCellCaching(): void
    {
        Settings::setCache(
            $this->cache->driver()
        );
    }
}
