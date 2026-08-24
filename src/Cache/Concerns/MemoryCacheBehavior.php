<?php

namespace Maatwebsite\Excel\Cache\Concerns;

use DateInterval;
use PhpOffice\PhpSpreadsheet\Cell\Cell;

/**
 * @internal
 */
trait MemoryCacheBehavior
{
    /**
     * @var array<string, mixed>
     */
    protected array $cache = [];

    public function __construct(
        protected ?int $memoryLimit = null,
    ) {
    }

    public function reachedMemoryLimit(): bool
    {
        // When no limit is given, we'll never reach any limit.
        if ($this->memoryLimit === null) {
            return false;
        }

        return count($this->cache) >= $this->memoryLimit;
    }

    /**
     * @return array<string, mixed>
     */
    public function flush(): array
    {
        $memory = $this->cache;

        foreach ($memory as $cell) {
            if ($cell instanceof Cell) {
                $cell->detach();
            }
        }

        $this->doClear();

        return $memory;
    }

    protected function doClear(): bool
    {
        $this->cache = [];

        return true;
    }

    protected function doDelete(string $key): bool
    {
        unset($this->cache[$key]);

        return true;
    }

    /**
     * @param  iterable<string>  $keys
     */
    protected function doDeleteMultiple(iterable $keys): bool
    {
        foreach ($keys as $key) {
            $this->doDelete($key);
        }

        return true;
    }

    /**
     * @param  mixed  $default
     * @return mixed
     */
    protected function doGet(string $key, $default = null)
    {
        if ($this->doHas($key)) {
            return $this->cache[$key];
        }

        return $default;
    }

    /**
     * @param  iterable<string>  $keys
     * @param  mixed  $default
     * @return array<string, mixed>
     */
    protected function doGetMultiple(iterable $keys, $default = null): array
    {
        $results = [];
        foreach ($keys as $key) {
            $results[$key] = $this->doGet($key, $default);
        }

        return $results;
    }

    protected function doHas(string $key): bool
    {
        return isset($this->cache[$key]);
    }

    /**
     * @param  mixed  $value
     * @param  null|int|DateInterval  $ttl
     */
    protected function doSet(string $key, $value, $ttl = null): bool
    {
        $this->cache[$key] = $value;

        return true;
    }

    /**
     * @param  iterable<string, mixed>  $values
     * @param  null|int|DateInterval  $ttl
     */
    protected function doSetMultiple(iterable $values, $ttl = null): bool
    {
        foreach ($values as $key => $value) {
            $this->doSet($key, $value);
        }

        return true;
    }
}
