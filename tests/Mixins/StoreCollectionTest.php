<?php

namespace Maatwebsite\Excel\Tests\Mixins;

use Illuminate\Support\Collection;
use Maatwebsite\Excel\Tests\TestCase;

class StoreCollectionTest extends TestCase
{
    /**
     * @test
     */
    public function can_store_a_collection_as_excel()
    {
        $collection = new Collection([
            ['test', 'test'],
            ['test', 'test'],
        ]);

        $response = $collection->storeExcel('collection-store.xlsx');

        $this->assertTrue($response);
        $this->assertFileExists(__DIR__ . '/../Data/Disks/Local/collection-store.xlsx');
    }

    /**
     * @test
     */
    public function can_store_a_collection_as_excel_on_non_default_disk()
    {
        $collection = new Collection([
            ['test', 'test'],
            ['test', 'test'],
        ]);

        $response = $collection->storeExcel('collection-store.xlsx', 'test');

        $this->assertTrue($response);
        $this->assertFileExists(__DIR__ . '/../Data/Disks/Test/collection-store.xlsx');
    }
}
