<?php

namespace Maatwebsite\Excel\Cache;

use Maatwebsite\Excel\Cache\Concerns\BatchCacheBehavior;
use Psr\SimpleCache\CacheInterface;

/**
 * Used when psr/simple-cache is ^1.0 or ^2.0.
 *
 * CacheInterface method signatures must stay untyped so they remain compatible
 * with all supported psr/simple-cache major versions. Do not add native types here.
 */
class BatchCacheDeprecated implements CacheInterface
{
    use BatchCacheBehavior;

    public function __construct(
        protected CacheInterface $cache,
        protected MemoryInterface $memory,
        int|\DateInterval|\DateTimeInterface|callable|null $defaultTTL = null
    ) {
        $this->defaultTTL = $defaultTTL;
    }

    /**
     * {@inheritdoc}
     */
    public function get($key, $default = null)
    {
        return $this->doGet($key, $default);
    }

    /**
     * @param  string  $key
     * @param  mixed  $value
     * @param  null|int|\DateInterval  $ttl
     */
    public function set($key, $value, $ttl = null)
    {
        return $this->doSet($key, $value, $ttl, func_num_args() === 3);
    }

    /**
     * {@inheritdoc}
     */
    public function delete($key)
    {
        return $this->doDelete($key);
    }

    /**
     * {@inheritdoc}
     */
    public function clear()
    {
        return $this->doClear();
    }

    /**
     * {@inheritdoc}
     *
     * @param  iterable<string>  $keys
     * @return iterable<string, mixed>
     */
    public function getMultiple($keys, $default = null)
    {
        return $this->doGetMultiple($keys, $default);
    }

    /**
     * @param  iterable<string, mixed>  $values
     * @param  null|int|\DateInterval  $ttl
     */
    public function setMultiple($values, $ttl = null)
    {
        return $this->doSetMultiple($values, $ttl, func_num_args() === 2);
    }

    /**
     * {@inheritdoc}
     *
     * @param  iterable<string>  $keys
     */
    public function deleteMultiple($keys)
    {
        return $this->doDeleteMultiple($keys);
    }

    /**
     * {@inheritdoc}
     */
    public function has($key)
    {
        return $this->doHas($key);
    }
}
