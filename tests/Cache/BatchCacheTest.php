<?php

declare(strict_types=1);

namespace Maatwebsite\Excel\Tests\Cache;

use Closure;
use Composer\InstalledVersions;
use Composer\Semver\VersionParser;
use DateInterval;
use Illuminate\Cache\ArrayStore;
use Illuminate\Cache\Events\KeyWritten;
use Illuminate\Cache\Repository;
use Illuminate\Contracts\Container\BindingResolutionException;
use Illuminate\Support\Facades\Event;
use Maatwebsite\Excel\Cache\BatchCache;
use Maatwebsite\Excel\Cache\BatchCacheDeprecated;
use Maatwebsite\Excel\Cache\CacheManager;
use Maatwebsite\Excel\Cache\MemoryInterface;
use Maatwebsite\Excel\Tests\TestCase;
use PHPUnit\Framework\Attributes\DataProvider;
use Psr\SimpleCache\CacheInterface;
use Psr\SimpleCache\InvalidArgumentException;
use Random\RandomException;

final class BatchCacheTest extends TestCase
{
    private Repository $cache;

    private MemoryInterface $memory;

    public function test_will_get_multiple_from_memory_if_cells_hold_in_memory(): void
    {
        $inMemory = [
            'A1' => 'A1-value',
            'A2' => 'A2-value',
            'A3' => 'A3-value',
        ];

        $cache = $this->givenCache($inMemory);

        $this->assertSame(
            $inMemory,
            $cache->getMultiple(['A1', 'A2', 'A3'])
        );

        $this->assertSame('A3-value', $cache->get('A3'));
    }

    public function test_will_get_multiple_from_cache_if_cells_are_persisted(): void
    {
        $inMemory  = [];
        $persisted = [
            'A1' => 'A1-value',
            'A2' => 'A2-value',
            'A3' => 'A3-value',
        ];

        $cache = $this->givenCache($inMemory, $persisted);

        $this->assertSame(
            $persisted,
            $cache->getMultiple(['A1', 'A2', 'A3'])
        );

        $this->assertSame('A3-value', $cache->get('A3'));
    }

    public function test_will_get_multiple_from_cache_and_persisted(): void
    {
        $inMemory = [
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

        $this->assertSame(
            array_merge($inMemory, $persisted),
            $cache->getMultiple(['A1', 'A2', 'A3', 'A4', 'A5', 'A6'])
        );

        $this->assertSame('A3-value', $cache->get('A3'));
        $this->assertSame('A6-value', $cache->get('A6'));
    }

    public function test_it_persists_to_cache_when_memory_limit_reached_on_setting_a_value(): void
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
        $this->assertSame([], array_filter($this->memory->getMultiple(['A1', 'A2', 'A3', 'A4'])));

        // All 4 cells show be persisted
        $this->assertSame([
            'A1' => 'A1-value',
            'A2' => 'A2-value',
            'A3' => 'A3-value',
            'A4' => 'A4-value',
        ], $this->cache->getMultiple(['A1', 'A2', 'A3', 'A4']));

        // Batch cache should return all 4 cells
        $this->assertSame([
            'A1' => 'A1-value',
            'A2' => 'A2-value',
            'A3' => 'A3-value',
            'A4' => 'A4-value',
        ], $cache->getMultiple(['A1', 'A2', 'A3', 'A4']));
    }

    public function test_it_persists_to_cache_when_memory_limit_reached_on_setting_multiple_values(): void
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
        $this->assertSame([], array_filter($this->memory->getMultiple(['A1', 'A2', 'A3', 'A4', 'A5'])));

        // All 4 cells show be persisted
        $this->assertSame([
            'A1' => 'A1-value',
            'A2' => 'A2-value',
            'A3' => 'A3-value',
            'A4' => 'A4-value',
            'A5' => 'A5-value',
        ], $this->cache->getMultiple(['A1', 'A2', 'A3', 'A4', 'A5']));

        // Batch cache should return all 4 cells
        $this->assertSame([
            'A1' => 'A1-value',
            'A2' => 'A2-value',
            'A3' => 'A3-value',
            'A4' => 'A4-value',
            'A5' => 'A5-value',
        ], $cache->getMultiple(['A1', 'A2', 'A3', 'A4', 'A5']));
    }

    #[DataProvider('defaultTTLDataProvider')]
    public function test_it_writes_to_cache_with_default_ttl(int|Closure|null $defaultTTL, int|Closure|null $receivedAs): void
    {
        config()->set('excel.cache.default_ttl', $defaultTTL);

        $cache = $this->givenCache(['A1' => 'A1-value'], [], 1);
        $this->cache->setEventDispatcher(Event::fake());
        $cache->set('A2', 'A2-value');

        $expectedTTL = value($receivedAs);

        $dispatchedCollection = Event::dispatched(
            KeyWritten::class,
            fn (KeyWritten $event): bool => $event->seconds === $expectedTTL
        );

        $this->assertCount(2, $dispatchedCollection);
    }

    public function test_it_writes_to_cache_with_a_dateinterval_ttl(): void
    {
        // DateInterval is 1 minute
        config()->set('excel.cache.default_ttl', new DateInterval('PT1M'));

        $cache = $this->givenCache(['A1' => 'A1-value'], [], 1);
        $this->cache->setEventDispatcher(Event::fake());
        $cache->set('A2', 'A2-value');

        $dispatchedCollection = Event::dispatched(
            KeyWritten::class,
            fn (KeyWritten $event): bool => $event->seconds >= 59 && $event->seconds <= 60
        );

        $this->assertCount(2, $dispatchedCollection);
    }

    public function test_it_can_override_default_ttl(): void
    {
        config()->set('excel.cache.default_ttl', 1);

        $cache = $this->givenCache(['A1' => 'A1-value'], [], 1);
        $this->cache->setEventDispatcher(Event::fake());
        $cache->set('A2', 'A2-value', null);

        $dispatchedCollection = Event::dispatched(
            KeyWritten::class,
            fn (KeyWritten $event): bool => $event->seconds === null
        );

        $this->assertCount(2, $dispatchedCollection);
    }

    public function test_delete_removes_value_held_in_memory(): void
    {
        $cache = $this->givenCache(['A1' => 'A1-value']);

        $this->assertTrue($cache->delete('A1'));
        $this->assertNull($cache->get('A1'));
    }

    public function test_delete_removes_value_from_persisted_cache_when_not_in_memory(): void
    {
        $cache = $this->givenCache([], ['A1' => 'A1-value']);

        $this->assertTrue($cache->delete('A1'));
        $this->assertNull($cache->get('A1'));
    }

    public function test_clear_empties_both_memory_and_persisted_cache(): void
    {
        $cache = $this->givenCache(['A1' => 'A1-value'], ['A2' => 'A2-value']);

        $this->assertTrue($cache->clear());
        $this->assertNull($cache->get('A1'));
        $this->assertNull($cache->get('A2'));
    }

    public function test_has_returns_true_when_value_is_in_memory(): void
    {
        $cache = $this->givenCache(['A1' => 'A1-value']);

        $this->assertTrue($cache->has('A1'));
    }

    public function test_has_returns_true_when_value_is_in_persisted_cache(): void
    {
        $cache = $this->givenCache([], ['A1' => 'A1-value']);

        $this->assertTrue($cache->has('A1'));
    }

    public function test_has_returns_false_when_value_is_absent(): void
    {
        $cache = $this->givenCache();

        $this->assertFalse($cache->has('A1'));
    }

    public function test_set_multiple_returns_true_without_persisting_when_memory_limit_not_reached(): void
    {
        $cache = $this->givenCache([], [], 10);

        $this->assertTrue($cache->setMultiple(['A1' => 'A1-value'], 10000));
        $this->assertNull($this->cache->get('A1'));
        $this->assertSame('A1-value', $cache->get('A1'));
    }

    public function test_set_multiple_uses_default_ttl_when_ttl_argument_is_omitted(): void
    {
        config()->set('excel.cache.default_ttl', 1);

        $cache = $this->givenCache([], [], 1);
        $this->cache->setEventDispatcher(Event::fake());
        $cache->setMultiple(['A2' => 'A2-value']);

        $dispatchedCollection = Event::dispatched(
            KeyWritten::class,
            fn (KeyWritten $event): bool => $event->seconds === 1
        );

        $this->assertCount(1, $dispatchedCollection);
    }

    public function test_batch_cache_survives_serialization_roundtrip(): void
    {
        $cache = $this->givenCache(['A1' => 'A1-value']);

        $unserialized = unserialize(serialize($cache));

        $this->assertSame('A1-value', $unserialized->get('A1'));
    }

    /**
     * @return array<string, array{int|Closure|null, int|Closure|null}>
     *
     * @throws RandomException
     */
    public static function defaultTTLDataProvider(): array
    {
        return [
            'null (forever)' => [null, null],
            'int value'      => [$value = random_int(1, 100), $value],
            'callable'       => [$closure = (fn (): int => 199), $closure],
        ];
    }

    /**
     * Construct a BatchCache with a in memory store
     * and an array cache, pretending to be a persistence store.
     *
     * @param  array<string, mixed>  $memory
     * @param  array<string, mixed>  $persisted
     *
     * @throws BindingResolutionException
     * @throws InvalidArgumentException
     */
    private function givenCache(array $memory = [], array $persisted = [], ?int $memoryLimit = null): CacheInterface
    {
        config()->set('excel.cache.batch.memory_limit', $memoryLimit ?: 60000);

        $this->memory = $this->app->make(CacheManager::class)->createMemoryDriver();
        $this->memory->setMultiple($memory);

        $store = new ArrayStore;
        $store->putMany($persisted, 10000);

        $this->cache = new Repository($store);

        if (!InstalledVersions::satisfies(new VersionParser, 'psr/simple-cache', '^3.0')) {
            return new BatchCacheDeprecated(
                $this->cache,
                $this->memory,
                config('excel.cache.default_ttl')
            );
        }

        return new BatchCache(
            $this->cache,
            $this->memory,
            config('excel.cache.default_ttl')
        );
    }
}
