<?php

namespace Maatwebsite\Excel\Tests;

use Maatwebsite\Excel\Excel;
use PhpOffice\PhpSpreadsheet\Settings;
use Maatwebsite\Excel\Cache\MemoryCache;
use Illuminate\Foundation\Console\Kernel;

class ExcelServiceProviderTest extends TestCase
{
    /**
     * @test
     */
    public function is_bound()
    {
        $this->assertTrue($this->app->bound('excel'));
    }

    /**
     * @test
     */
    public function has_aliased()
    {
        $this->assertTrue($this->app->isAlias(Excel::class));
        $this->assertEquals('excel', $this->app->getAlias(Excel::class));
    }

    /**
     * @test
     */
    public function registers_console_commands()
    {
        /** @var Kernel $kernel */
        $kernel   = $this->app->make(Kernel::class);
        $commands = $kernel->all();

        $this->assertArrayHasKey('make:export', $commands);
        $this->assertArrayHasKey('make:import', $commands);
    }

    /**
     * @test
     */
    public function sets_php_spreadsheet_settings()
    {
        $driver = config('excel.cache.driver');

        $this->assertEquals('memory', $driver);
        $this->assertInstanceOf(
            MemoryCache::class,
            Settings::getCache()
        );
    }
}
