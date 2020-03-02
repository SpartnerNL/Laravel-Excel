<?php

namespace Maatwebsite\Excel\Cache;

use Illuminate\Support\Manager;
use Maatwebsite\Excel\Config\Configuration;
use Psr\SimpleCache\CacheInterface;

class CacheManager extends Manager
{
    /**
     * @const string
     */
    const DRIVER_BATCH = 'batch';

    /**
     * @const string
     */
    const DRIVER_MEMORY = 'memory';

    /**
     * @const string
     */
    const DRIVER_ILLUMINATE = 'illuminate';

    /**
     * Get the default driver name.
     *
     * @return string
     */
    public function getDefaultDriver(): string
    {
        return Configuration::getCellCacheDriver();
    }

    /**
     * @return MemoryCache
     */
    public function createMemoryDriver(): CacheInterface
    {
        return new MemoryCache(
            Configuration::getBatchMemoryLimit()
        );
    }

    /**
     * @return BatchCache
     */
    public function createBatchDriver(): CacheInterface
    {
        return new BatchCache(
            $this->createIlluminateDriver(),
            $this->createMemoryDriver()
        );
    }

    /**
     * @return CacheInterface
     */
    public function createIlluminateDriver(): CacheInterface
    {
        return $this->app->make('cache')->driver(
            Configuration::getIlluminateCacheStore()
        );
    }
}
