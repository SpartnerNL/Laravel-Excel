<?php

namespace Maatwebsite\Excel\Config;

use Maatwebsite\Excel\Cache\CacheManager;
use Maatwebsite\Excel\DefaultValueBinder;
use PhpOffice\PhpSpreadsheet\Cell\IValueBinder;

class Configuration
{
    /**
     * @return IValueBinder
     */
    public static function getDefaultValueBinder(): IValueBinder
    {
        return app(
            config('excel.value_binder.default', DefaultValueBinder::class)
        );
    }

    /**
     * TODO: deprecate "excel.exports.temp_path" fallback in 3.2.
     *
     * @return string
     */
    public static function getLocalTemporaryPath(): string
    {
        return config(
            'excel.temporary_files.local_path',
            config('excel.exports.temp_path', sys_get_temp_dir())
        );
    }

    /**
     * @return string|null
     */
    public static function getRemoteTemporaryDisk()
    {
        return config('excel.temporary_files.remote_disk');
    }

    /**
     * @return string|null
     */
    public static function getTransactionHandler()
    {
        return config('excel.transactions.handler');
    }

    /**
     * @return string
     */
    public static function getCellCacheDriver(): string
    {
        return config('excel.cache.driver', 'memory');
    }

    /**
     * @return string|null
     */
    public static function getIlluminateCacheStore()
    {
        return config('excel.cache.illuminate.store');
    }

    /**
     * @return int
     */
    public static function getBatchMemoryLimit(): int
    {
        return config('excel.cache.batch.memory_limit', 60000);
    }

    /**
     * @return bool
     */
    public static function usesDiskCache(): bool
    {
        return self::getCellCacheDriver() !== CacheManager::DRIVER_MEMORY;
    }
}
