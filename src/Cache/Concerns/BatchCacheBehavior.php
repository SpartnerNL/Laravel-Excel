<?php

namespace Maatwebsite\Excel\Cache\Concerns;

use DateInterval;
use DateTimeInterface;
use Illuminate\Support\Facades\Cache;

/**
 * @internal
 */
trait BatchCacheBehavior
{
    /**
     * @var null|int|DateInterval|DateTimeInterface|callable
     */
    protected $defaultTTL;

    /**
     * @return array<int, string>
     */
    public function __sleep(): array
    {
        return ['memory'];
    }

    public function __wakeup(): void
    {
        $this->cache = Cache::driver(
            config('excel.cache.illuminate.store')
        );
    }

    /**
     * @param  mixed  $default
     * @return mixed
     */
    protected function doGet(string $key, $default = null)
    {
        if ($this->memory->has($key)) {
            return $this->memory->get($key);
        }

        return $this->cache->get($key, $default);
    }

    /**
     * @param  mixed  $value
     * @param  null|int|DateInterval  $ttl
     */
    protected function doSet(string $key, $value, $ttl, bool $ttlProvided): bool
    {
        if (!$ttlProvided) {
            $ttl = value($this->defaultTTL);
        }

        $this->memory->set($key, $value, $ttl);

        if ($this->memory->reachedMemoryLimit()) {
            return $this->cache->setMultiple($this->memory->flush(), $ttl);
        }

        return true;
    }

    protected function doDelete(string $key): bool
    {
        if ($this->memory->has($key)) {
            return $this->memory->delete($key);
        }

        return $this->cache->delete($key);
    }

    protected function doClear(): bool
    {
        $this->memory->clear();

        return $this->cache->clear();
    }

    /**
     * @param  iterable<string>  $keys
     * @param  mixed  $default
     * @return iterable<string, mixed>
     */
    protected function doGetMultiple(iterable $keys, $default = null): iterable
    {
        $keys = [...$keys];

        // Check if all keys are still in memory
        $memory = $this->memory->getMultiple($keys, $default);
        if (!is_array($memory)) {
            $memory = iterator_to_array($memory);
        }
        $actualItemsInMemory = count(array_filter($memory));

        if ($actualItemsInMemory === count($keys)) {
            return $memory;
        }

        // Get all rows from cache if none is hold in memory.
        if ($actualItemsInMemory === 0) {
            return $this->cache->getMultiple($keys, $default);
        }

        // Add missing values from cache.
        foreach ($this->cache->getMultiple($keys, $default) as $key => $value) {
            if ($value !== null) {
                $memory[$key] = $value;
            }
        }

        return $memory;
    }

    /**
     * @param  iterable<string, mixed>  $values
     * @param  null|int|DateInterval  $ttl
     */
    protected function doSetMultiple(iterable $values, $ttl, bool $ttlProvided): bool
    {
        if (!$ttlProvided) {
            $ttl = value($this->defaultTTL);
        }

        $this->memory->setMultiple($values, $ttl);

        if ($this->memory->reachedMemoryLimit()) {
            return $this->cache->setMultiple($this->memory->flush(), $ttl);
        }

        return true;
    }

    /**
     * @param  iterable<string>  $keys
     */
    protected function doDeleteMultiple(iterable $keys): bool
    {
        $keys = [...$keys];

        $this->memory->deleteMultiple($keys);

        return $this->cache->deleteMultiple($keys);
    }

    protected function doHas(string $key): bool
    {
        if ($this->memory->has($key)) {
            return true;
        }

        return $this->cache->has($key);
    }
}
