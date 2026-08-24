<?php

namespace Maatwebsite\Excel\Cache;

use Maatwebsite\Excel\Cache\Concerns\MemoryCacheBehavior;

/**
 * Used when psr/simple-cache is ^1.0 or ^2.0.
 *
 * CacheInterface method signatures must stay untyped so they remain compatible
 * with all supported psr/simple-cache major versions. Do not add native types here.
 */
class MemoryCacheDeprecated implements MemoryInterface
{
    use MemoryCacheBehavior;

    /**
     * {@inheritdoc}
     */
    public function clear()
    {
        return $this->doClear();
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
    public function get($key, $default = null)
    {
        return $this->doGet($key, $default);
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
     * {@inheritdoc}
     */
    public function has($key)
    {
        return $this->doHas($key);
    }

    /**
     * @param  string  $key
     * @param  mixed  $value
     * @param  null|int|\DateInterval  $ttl
     */
    public function set($key, $value, $ttl = null)
    {
        return $this->doSet($key, $value, $ttl);
    }

    /**
     * @param  iterable<string, mixed>  $values
     * @param  null|int|\DateInterval  $ttl
     */
    public function setMultiple($values, $ttl = null)
    {
        return $this->doSetMultiple($values, $ttl);
    }
}
