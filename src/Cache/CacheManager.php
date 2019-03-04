<?php

namespace Maatwebsite\Excel\Cache;

use Illuminate\Support\Manager;
use Psr\SimpleCache\CacheInterface;

class CacheManager extends Manager
{
    /**
     * Get the default driver name.
     *
     * @return string
     */
    public function getDefaultDriver(): string
    {
        return config('excel.cache.driver', 'memory');
    }

    /**
     * @return MemoryCache
     */
    public function createMemoryDriver(): CacheInterface
    {
        return new MemoryCache(
            config('excel.cache.hybrid.memory_limit', 60000)
        );
    }

    /**
     * @return HybridCache
     */
    public function createHybridDriver(): CacheInterface
    {
        return new HybridCache(
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
            config('excel.cache.illuminate.store')
        );
    }
}
