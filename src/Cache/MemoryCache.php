<?php

namespace Maatwebsite\Excel\Cache;

use DateInterval;
use PhpOffice\PhpSpreadsheet\Cell\Cell;

class MemoryCache implements MemoryInterface
{
    /**
     * @var array<string, mixed>
     */
    protected array $cache = [];

    public function __construct(
        protected ?int $memoryLimit = null,
    ) {
    }

    /**
     * {@inheritdoc}
     */
    public function clear(): bool
    {
        $this->cache = [];

        return true;
    }

    /**
     * {@inheritdoc}
     */
    public function delete(string $key): bool
    {
        unset($this->cache[$key]);

        return true;
    }

    /**
     * {@inheritdoc}
     */
    public function deleteMultiple($keys): bool
    {
        foreach ($keys as $key) {
            $this->delete($key);
        }

        return true;
    }

    /**
     * {@inheritdoc}
     */
    public function get(string $key, mixed $default = null): mixed
    {
        if ($this->has($key)) {
            return $this->cache[$key];
        }

        return $default;
    }

    /**
     * {@inheritdoc}
     */
    public function getMultiple(iterable $keys, mixed $default = null): iterable
    {
        $results = [];
        foreach ($keys as $key) {
            $results[$key] = $this->get($key, $default);
        }

        return $results;
    }

    /**
     * {@inheritdoc}
     */
    public function has($key): bool
    {
        return isset($this->cache[$key]);
    }

    /**
     * {@inheritdoc}
     */
    public function set(string $key, mixed $value, null|int|DateInterval $ttl = null): bool
    {
        $this->cache[$key] = $value;

        return true;
    }

    /**
     * {@inheritdoc}
     *
     * @param  iterable<string, mixed>  $values
     */
    public function setMultiple($values, $ttl = null): bool
    {
        foreach ($values as $key => $value) {
            $this->set($key, $value);
        }

        return true;
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

        $this->clear();

        return $memory;
    }
}
