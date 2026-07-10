<?php

namespace Maatwebsite\Excel\Cache;

use Illuminate\Support\Facades\Cache;
use Psr\SimpleCache\CacheInterface;

/**
 * Used when psr/simple-cache is ^1.0 or ^2.0.
 *
 * CacheInterface method signatures must stay untyped so they remain compatible
 * with all supported psr/simple-cache major versions. Do not add native types here.
 */
class BatchCacheDeprecated implements CacheInterface
{
    /**
     * @var null|int|\DateInterval|\DateTimeInterface|callable
     */
    protected $defaultTTL = null;

    public function __construct(
        protected CacheInterface $cache,
        protected MemoryInterface $memory,
        int|\DateInterval|\DateTimeInterface|callable|null $defaultTTL = null
    ) {
        $this->defaultTTL = $defaultTTL;
    }

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
     * {@inheritdoc}
     */
    public function get($key, $default = null)
    {
        if ($this->memory->has($key)) {
            return $this->memory->get($key);
        }

        return $this->cache->get($key, $default);
    }

    /**
     * @param  string  $key
     * @param  mixed  $value
     * @param  null|int|\DateInterval  $ttl
     */
    public function set($key, $value, $ttl = null)
    {
        if (func_num_args() === 2) {
            $ttl = value($this->defaultTTL);
        }

        $this->memory->set($key, $value, $ttl);

        if ($this->memory->reachedMemoryLimit()) {
            return $this->cache->setMultiple($this->memory->flush(), $ttl);
        }

        return true;
    }

    /**
     * {@inheritdoc}
     */
    public function delete($key)
    {
        if ($this->memory->has($key)) {
            return $this->memory->delete($key);
        }

        return $this->cache->delete($key);
    }

    /**
     * {@inheritdoc}
     */
    public function clear()
    {
        $this->memory->clear();

        return $this->cache->clear();
    }

    /**
     * {@inheritdoc}
     *
     * @param  iterable<string>  $keys
     * @return iterable<string, mixed>
     */
    public function getMultiple($keys, $default = null)
    {
        // Check if all keys are still in memory
        $memory = $this->memory->getMultiple($keys, $default);
        if (is_array($memory)) {
            $actualItemsInMemory = count(array_filter($memory));
        } else {
            $actualItemsInMemory = 0;
            foreach ($memory as $value) {
                if ($value) {
                    $actualItemsInMemory++;
                }
            }
        }

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
     * @param  null|int|\DateInterval  $ttl
     */
    public function setMultiple($values, $ttl = null)
    {
        if (func_num_args() === 1) {
            $ttl = value($this->defaultTTL);
        }

        $this->memory->setMultiple($values, $ttl);

        if ($this->memory->reachedMemoryLimit()) {
            return $this->cache->setMultiple($this->memory->flush(), $ttl);
        }

        return true;
    }

    /**
     * {@inheritdoc}
     *
     * @param  iterable<string>  $keys
     */
    public function deleteMultiple($keys)
    {
        $keys = is_array($keys) ? $keys : iterator_to_array($keys);

        $this->memory->deleteMultiple($keys);

        return $this->cache->deleteMultiple($keys);
    }

    /**
     * {@inheritdoc}
     */
    public function has($key)
    {
        if ($this->memory->has($key)) {
            return true;
        }

        return $this->cache->has($key);
    }
}
