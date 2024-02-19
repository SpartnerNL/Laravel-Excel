<?php

namespace Maatwebsite\Excel\Tests\Mixins;

use Maatwebsite\Excel\Excel;
use Maatwebsite\Excel\Tests\Data\Stubs\Database\User;
use Maatwebsite\Excel\Tests\TestCase;

class StoreQueryMacroTest extends TestCase
{
    /**
     * Setup the test environment.
     */
    protected function setUp(): void
    {
        parent::setUp();

        $this->loadLaravelMigrations(['--database' => 'testing']);
        $this->withFactories(__DIR__ . '/../Data/Stubs/Database/Factories');

        factory(User::class)->times(100)->create();
    }

    public function test_can_download_a_query_as_excel()
    {
        $response = User::storeExcel('query-store.xlsx', null, Excel::XLSX);

        $this->assertTrue($response);
        $this->assertFileExists(__DIR__ . '/../Data/Disks/Local/query-store.xlsx');

        $array = $this->readAsArray(__DIR__ . '/../Data/Disks/Local/query-store.xlsx', Excel::XLSX);
        $this->assertCount(100, $array);
    }

    public function test_can_download_a_query_as_excel_on_different_disk()
    {
        $response = User::storeExcel('query-store.xlsx', 'test', Excel::XLSX);

        $this->assertTrue($response);
        $this->assertFileExists(__DIR__ . '/../Data/Disks/Test/query-store.xlsx');

        $array = $this->readAsArray(__DIR__ . '/../Data/Disks/Test/query-store.xlsx', Excel::XLSX);
        $this->assertCount(100, $array);
    }

    public function test_can_store_a_query_with_headers_as_excel()
    {
        $response = User::storeExcel('query-headers-store.xlsx', null, Excel::XLSX, true);

        $this->assertTrue($response);
        $this->assertFileExists(__DIR__ . '/../Data/Disks/Local/query-headers-store.xlsx');

        $array = $this->readAsArray(__DIR__ . '/../Data/Disks/Local/query-headers-store.xlsx', Excel::XLSX);
        $this->assertCount(101, $array);
    }
}
