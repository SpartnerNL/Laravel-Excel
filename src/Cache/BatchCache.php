<?php

declare(strict_types=1);

namespace Maatwebsite\Excel\Cache;

use DateInterval;
use Maatwebsite\Excel\Cache\Concerns\BatchCacheBehavior;
use Psr\SimpleCache\CacheInterface;

class BatchCache implements CacheInterface
{
    use BatchCacheBehavior;

    public function __construct(
        protected CacheInterface $cache,
        protected MemoryInterface $memory,
        null|int|DateInterval|callable $defaultTTL = null
    ) {
        $this->defaultTTL = $defaultTTL;
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
    public function set(string $key, mixed $value, null|int|DateInterval $ttl = null): bool
    {
        return $this->doSet($key, $value, $ttl, func_num_args() === 3);
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
    public function clear(): bool
    {
        return $this->doClear();
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
     *
     * @param  iterable<string, mixed>  $values
     */
    public function setMultiple(iterable $values, null|int|DateInterval $ttl = null): bool
    {
        return $this->doSetMultiple($values, $ttl, func_num_args() === 2);
    }

    /**
     * {@inheritdoc}
     */
    public function deleteMultiple(iterable $keys): bool
    {
        return $this->doDeleteMultiple($keys);
    }

    /**
     * {@inheritdoc}
     */
    public function has(string $key): bool
    {
        return $this->doHas($key);
    }
}
