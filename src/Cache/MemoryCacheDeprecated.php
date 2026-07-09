<?php

namespace Maatwebsite\Excel\Cache;

use PhpOffice\PhpSpreadsheet\Cell\Cell;

/**
 * Used when psr/simple-cache is ^1.0 or ^2.0.
 *
 * CacheInterface method signatures must stay untyped so they remain compatible
 * with all supported psr/simple-cache major versions. Do not add native types here.
 */
class MemoryCacheDeprecated implements MemoryInterface
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
    public function clear()
    {
        $this->cache = [];

        return true;
    }

    /**
     * {@inheritdoc}
     */
    public function delete($key)
    {
        unset($this->cache[$key]);

        return true;
    }

    /**
     * {@inheritdoc}
     *
     * @param  iterable<string>  $keys
     */
    public function deleteMultiple($keys)
    {
        foreach ($keys as $key) {
            $this->delete($key);
        }

        return true;
    }

    /**
     * {@inheritdoc}
     */
    public function get($key, $default = null)
    {
        if ($this->has($key)) {
            return $this->cache[$key];
        }

        return $default;
    }

    /**
     * {@inheritdoc}
     *
     * @param  iterable<string>  $keys
     * @return iterable<string, mixed>
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
     * {@inheritdoc}
     */
    public function has($key)
    {
        return isset($this->cache[$key]);
    }

    /**
     * @param  string  $key
     * @param  mixed  $value
     * @param  null|int|\DateInterval  $ttl
     */
    public function set($key, $value, $ttl = null)
    {
        $this->cache[$key] = $value;

        return true;
    }

    /**
     * @param  iterable<string, mixed>  $values
     * @param  null|int|\DateInterval  $ttl
     */
    public function setMultiple($values, $ttl = null)
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
