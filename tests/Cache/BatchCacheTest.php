<?php

namespace Maatwebsite\Excel\Tests\Cache;

use Illuminate\Cache\ArrayStore;
use Illuminate\Cache\Repository;
use Maatwebsite\Excel\Cache\BatchCache;
use Maatwebsite\Excel\Cache\MemoryCache;
use Maatwebsite\Excel\Tests\TestCase;

class BatchCacheTest extends TestCase
{
    /**
     * @var Repository
     */
    private $cache;

    /**
     * @var MemoryCache
     */
    private $memory;

    /**
     * @test
     */
    public function will_get_multiple_from_memory_if_cells_hold_in_memory()
    {
        $inMemory = [
            'A1' => 'A1-value',
            'A2' => 'A2-value',
            'A3' => 'A3-value',
        ];

        $cache = $this->givenCache($inMemory);

        $this->assertEquals(
            $inMemory,
            $cache->getMultiple(['A1', 'A2', 'A3'])
        );

        $this->assertEquals('A3-value', $cache->get('A3'));
    }

    /**
     * @test
     */
    public function will_get_multiple_from_cache_if_cells_are_persisted()
    {
        $inMemory  = [];
        $persisted = [
            'A1' => 'A1-value',
            'A2' => 'A2-value',
            'A3' => 'A3-value',
        ];

        $cache = $this->givenCache($inMemory, $persisted);

        $this->assertEquals(
            $persisted,
            $cache->getMultiple(['A1', 'A2', 'A3'])
        );

        $this->assertEquals('A3-value', $cache->get('A3'));
    }

    /**
     * @test
     */
    public function will_get_multiple_from_cache_and_persisted()
    {
        $inMemory  = [
            'A1' => 'A1-value',
            'A2' => 'A2-value',
            'A3' => 'A3-value',
        ];
        $persisted = [
            'A4' => 'A4-value',
            'A5' => 'A5-value',
            'A6' => 'A6-value',
        ];

        $cache = $this->givenCache($inMemory, $persisted);

        $this->assertEquals(
            array_merge($inMemory, $persisted),
            $cache->getMultiple(['A1', 'A2', 'A3', 'A4', 'A5', 'A6'])
        );

        $this->assertEquals('A3-value', $cache->get('A3'));
        $this->assertEquals('A6-value', $cache->get('A6'));
    }

    /**
     * @test
     */
    public function it_persists_to_cache_when_memory_limit_reached_on_setting_a_value()
    {
        $memoryLimit = 3;
        $persisted   = [];
        $inMemory    = [
            'A1' => 'A1-value',
            'A2' => 'A2-value',
            'A3' => 'A3-value',
        ];

        $cache = $this->givenCache($inMemory, $persisted, $memoryLimit);

        // Setting a 4th value will reach the memory limit
        $cache->set('A4', 'A4-value', 10000);

        // Nothing in memory anymore
        $this->assertEquals([], array_filter($this->memory->getMultiple(['A1', 'A2', 'A3', 'A4'])));

        // All 4 cells show be persisted
        $this->assertEquals([
            'A1' => 'A1-value',
            'A2' => 'A2-value',
            'A3' => 'A3-value',
            'A4' => 'A4-value',
        ], $this->cache->getMultiple(['A1', 'A2', 'A3', 'A4']));

        // Batch cache should return all 4 cells
        $this->assertEquals([
            'A1' => 'A1-value',
            'A2' => 'A2-value',
            'A3' => 'A3-value',
            'A4' => 'A4-value',
        ], $cache->getMultiple(['A1', 'A2', 'A3', 'A4']));
    }

    /**
     * @test
     */
    public function it_persists_to_cache_when_memory_limit_reached_on_setting_multiple_values()
    {
        $memoryLimit = 3;
        $persisted   = [];
        $inMemory    = [
            'A1' => 'A1-value',
            'A2' => 'A2-value',
            'A3' => 'A3-value',
        ];

        $cache = $this->givenCache($inMemory, $persisted, $memoryLimit);

        // Setting a 4th value will reach the memory limit
        $cache->setMultiple([
            'A4' => 'A4-value',
            'A5' => 'A5-value',
        ], 10000);

        // Nothing in memory anymore
        $this->assertEquals([], array_filter($this->memory->getMultiple(['A1', 'A2', 'A3', 'A4', 'A5'])));

        // All 4 cells show be persisted
        $this->assertEquals([
            'A1' => 'A1-value',
            'A2' => 'A2-value',
            'A3' => 'A3-value',
            'A4' => 'A4-value',
            'A5' => 'A5-value',
        ], $this->cache->getMultiple(['A1', 'A2', 'A3', 'A4', 'A5']));

        // Batch cache should return all 4 cells
        $this->assertEquals([
            'A1' => 'A1-value',
            'A2' => 'A2-value',
            'A3' => 'A3-value',
            'A4' => 'A4-value',
            'A5' => 'A5-value',
        ], $cache->getMultiple(['A1', 'A2', 'A3', 'A4', 'A5']));
    }

    /**
     * Construct a BatchCache with a in memory store
     * and an array cache, pretending to be a persistence store.
     *
     * @param array    $memory
     * @param array    $persisted
     * @param int|null $memoryLimit
     *
     * @return BatchCache
     */
    private function givenCache(array $memory = [], array $persisted = [], int $memoryLimit = null): BatchCache
    {
        $this->memory = new MemoryCache($memoryLimit);
        $this->memory->setMultiple($memory);

        $store = new ArrayStore();
        $store->putMany($persisted, 10000);

        $this->cache = new Repository($store);

        return new BatchCache(
            $this->cache,
            $this->memory
        );
    }
}
