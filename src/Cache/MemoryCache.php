<?php

declare(strict_types=1);

namespace Maatwebsite\Excel\Cache;

use DateInterval;
use Maatwebsite\Excel\Cache\Concerns\MemoryCacheBehavior;

class MemoryCache implements MemoryInterface
{
    use MemoryCacheBehavior;

    /**
     * {@inheritdoc}
     */
    public function clear(): bool
    {
        return $this->doClear();
    }

    /**
     * {@inheritdoc}
     */
    public function delete(string $key): bool
    {
        return $this->doDelete($key);
    }

    /**
     * {@inheritdoc}
     */
    public function deleteMultiple($keys): bool
    {
        return $this->doDeleteMultiple($keys);
    }

    /**
     * {@inheritdoc}
     */
    public function get(string $key, mixed $default = null): mixed
    {
        return $this->doGet($key, $default);
    }

    /**
     * {@inheritdoc}
     */
    public function getMultiple(iterable $keys, mixed $default = null): iterable
    {
        return $this->doGetMultiple($keys, $default);
    }

    /**
     * {@inheritdoc}
     */
    public function has($key): bool
    {
        return $this->doHas($key);
    }

    /**
     * {@inheritdoc}
     */
    public function set(string $key, mixed $value, null|int|DateInterval $ttl = null): bool
    {
        return $this->doSet($key, $value, $ttl);
    }

    /**
     * {@inheritdoc}
     *
     * @param  iterable<string, mixed>  $values
     */
    public function setMultiple($values, $ttl = null): bool
    {
        return $this->doSetMultiple($values, $ttl);
    }
}
