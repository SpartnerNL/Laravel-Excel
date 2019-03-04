<?php

namespace Maatwebsite\Excel\Cache;

use Psr\SimpleCache\CacheInterface;

class MemoryCache implements CacheInterface
{
    /**
     * @var int|null
     */
    protected $limit;

    /**
     * @var array
     */
    protected $cache = [];

    /**
     * @param int|null $limit
     */
    public function __construct(int $limit = null)
    {
        $this->limit = $limit;
    }

    /**
     * @inheritdoc
     */
    public function clear()
    {
        $this->cache = [];

        return true;
    }

    /**
     * @inheritdoc
     */
    public function delete($key)
    {
        unset($this->cache[$key]);

        return true;
    }

    /**
     * @inheritdoc
     */
    public function deleteMultiple($keys)
    {
        foreach ($keys as $key) {
            $this->delete($key);
        }

        return true;
    }

    /**
     * @inheritdoc
     */
    public function get($key, $default = null)
    {
        if ($this->has($key)) {
            return $this->cache[$key];
        }

        return $default;
    }

    /**
     * @inheritdoc
     */
    public function getMultiple($keys, $default = null)
    {
        $results = [];
        foreach ($keys as $key) {
            $results[$key] = $this->get($key, $default);
        }

        return $results;
    }

    /**
     * @inheritdoc
     */
    public function has($key)
    {
        return isset($this->cache[$key]);
    }

    /**
     * @inheritdoc
     */
    public function set($key, $value, $ttl = null)
    {
        $this->cache[$key] = $value;

        return true;
    }

    /**
     * @inheritdoc
     */
    public function setMultiple($values, $ttl = null)
    {
        foreach ($values as $key => $value) {
            $this->set($key, $value);
        }

        return true;
    }

    /**
     * @return bool
     */
    public function reachedLimit(): bool
    {
        if (null === $this->limit) {
            return false;
        }

        return count($this->cache) >= $this->limit;
    }

    /**
     * @return array
     */
    public function flush(): array
    {
        $memory = $this->cache;

        $this->clear();

        return $memory;
    }
}