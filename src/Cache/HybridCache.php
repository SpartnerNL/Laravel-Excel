<?php

namespace Maatwebsite\Excel\Cache;

use Psr\SimpleCache\CacheInterface;

class HybridCache implements CacheInterface
{
    /**
     * @var CacheInterface
     */
    protected $cache;

    /**
     * @var MemoryCache
     */
    protected $memory;

    /**
     * @param CacheInterface $cache
     * @param MemoryCache    $memory
     */
    public function __construct(CacheInterface $cache, MemoryCache $memory)
    {
        $this->cache  = $cache;
        $this->memory = $memory;
    }

    /**
     * @inheritdoc
     */
    public function get($key, $default = null)
    {
        if ($this->memory->has($key)) {
            return $this->memory->get($key);
        }

        return $this->cache->get($key, $default);
    }

    /**
     * @inheritdoc
     */
    public function set($key, $value, $ttl = null)
    {
        $this->memory->set($key, $value, $ttl);

        if ($this->memory->reachedLimit()) {
            return $this->cache->setMultiple($this->memory->flush(), $ttl);
        }

        return true;
    }

    /**
     * @inheritdoc
     */
    public function delete($key)
    {
        if ($this->memory->has($key)) {
            return $this->memory->delete($key);
        }

        return $this->cache->delete($key);
    }

    /**
     * @inheritdoc
     */
    public function clear()
    {
        $this->memory->clear();

        return $this->cache->clear();
    }

    /**
     * @inheritdoc
     */
    public function getMultiple($keys, $default = null)
    {
        // Check if all keys are still in memory
        $memory = $this->getMultiple($keys, $default);
        if (count($memory) === count($keys)) {
            return $memory;
        }

        // Get all rows from cache if none is hold in memory.
        if (count($memory) === 0) {
            return $this->cache->getMultiple($keys, $default);
        }

        // Merge memory and cache rows
        return array_merge(
            $memory,
            $this->cache->getMultiple($keys, $default)
        );
    }

    /**
     * @inheritdoc
     */
    public function setMultiple($values, $ttl = null)
    {
        $this->memory->setMultiple($values, $ttl);

        if ($this->memory->reachedLimit()) {
            return $this->cache->setMultiple($this->memory->flush(), $ttl);
        }

        return true;
    }

    /**
     * @inheritdoc
     */
    public function deleteMultiple($keys)
    {
        $keys = is_array($keys) ? $keys : iterator_to_array($keys);

        $this->memory->deleteMultiple($keys);

        return $this->cache->deleteMultiple($keys);
    }

    /**
     * @inheritdoc
     */
    public function has($key)
    {
        if ($this->memory->has($key)) {
            return true;
        }

        return $this->cache->has($key);
    }
}