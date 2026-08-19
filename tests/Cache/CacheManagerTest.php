<?php

declare(strict_types=1);

namespace Maatwebsite\Excel\Tests\Cache;

use Maatwebsite\Excel\Cache\CacheManager;
use Maatwebsite\Excel\Tests\TestCase;

final class CacheManagerTest extends TestCase
{
    public function test_is_in_memory_returns_true_for_default_memory_driver(): void
    {
        config()->set('excel.cache.driver', 'memory');

        $this->assertTrue(app(CacheManager::class)->isInMemory());
    }

    public function test_is_in_memory_returns_false_for_other_drivers(): void
    {
        config()->set('excel.cache.driver', 'illuminate');

        $this->assertFalse(app(CacheManager::class)->isInMemory());
    }

    public function test_flush_clears_the_default_driver(): void
    {
        config()->set('excel.cache.driver', 'memory');

        $manager = app(CacheManager::class);
        $driver  = $manager->driver();
        $driver->set('A1', 'value');

        $this->assertTrue($driver->has('A1'));

        $manager->flush();

        $this->assertFalse($driver->has('A1'));
    }
}
