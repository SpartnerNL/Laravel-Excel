<?php

declare(strict_types=1);

namespace Maatwebsite\Excel\Tests;

use Composer\InstalledVersions;
use Composer\Semver\VersionParser;
use Illuminate\Contracts\Console\Kernel;
use Maatwebsite\Excel\Cache\MemoryCache;
use Maatwebsite\Excel\Cache\MemoryCacheDeprecated;
use Maatwebsite\Excel\Excel;
use Maatwebsite\Excel\Tests\Data\Stubs\CustomTransactionHandler;
use Maatwebsite\Excel\Transactions\TransactionManager;
use PhpOffice\PhpSpreadsheet\Settings;

final class ExcelServiceProviderTest extends TestCase
{
    public function test_custom_transaction_handler_is_bound(): void
    {
        $this->app->make(TransactionManager::class)->extend('handler', fn (): CustomTransactionHandler => new CustomTransactionHandler);

        $this->assertInstanceOf(CustomTransactionHandler::class, $this->app->make(TransactionManager::class)->driver('handler'));
    }

    public function test_is_bound(): void
    {
        $this->assertTrue($this->app->bound('excel'));
    }

    public function test_has_aliased(): void
    {
        $this->assertTrue($this->app->isAlias(Excel::class));
        $this->assertEquals('excel', $this->app->getAlias(Excel::class));
    }

    public function test_registers_console_commands(): void
    {
        /** @var Kernel $kernel */
        $kernel   = $this->app->make(Kernel::class);
        $commands = $kernel->all();

        $this->assertArrayHasKey('make:export', $commands);
        $this->assertArrayHasKey('make:import', $commands);
    }

    public function test_sets_php_spreadsheet_settings(): void
    {
        $driver = config('excel.cache.driver');

        $this->assertEquals('memory', $driver);

        if (InstalledVersions::satisfies(new VersionParser, 'psr/simple-cache', '^3.0')) {
            $this->assertInstanceOf(
                MemoryCache::class,
                Settings::getCache()
            );
        } else {
            $this->assertInstanceOf(
                MemoryCacheDeprecated::class,
                Settings::getCache()
            );
        }
    }
}
