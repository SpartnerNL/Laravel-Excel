<?php

namespace Maatwebsite\Excel\Tests\Mixins;

use Maatwebsite\Excel\Excel;
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

    /**
     * @test
     */
    public function can_store_a_collection_with_headings_as_excel()
    {
        $collection = new Collection([
            ['column_1' => 'test', 'column_2' => 'test'],
            ['column_1' => 'test', 'column_2' => 'test'],
        ]);

        $response = $collection->storeExcel('collection-headers-store.xlsx', null, Excel::XLSX, true);

        $file = __DIR__ . '/../Data/Disks/Local/collection-headers-store.xlsx';

        $this->assertTrue($response);
        $this->assertFileExists($file);

        if (is_file($file)) {
            $array = $this->readAsArray($file, Excel::XLSX);
            $this->assertEquals(['column_1', 'column_2'], collect($array)->first());

            $this->assertEquals([
                ['test', 'test'],
                ['test', 'test'],
            ], collect($array)->except(0)->values()->all());
        }
    }

    /**
     * @test
     */
    public function can_store_a_collection_without_headings_as_excel()
    {
        $collection = new Collection([
            ['column_1' => 'test', 'column_2' => 'test'],
            ['column_1' => 'test2', 'column_2' => 'test2'],
        ]);

        $response = $collection->storeExcel('collection-without-headers-store.xlsx', null, Excel::XLSX, false);

        $file = __DIR__ . '/../Data/Disks/Local/collection-without-headers-store.xlsx';

        $this->assertTrue($response);
        $this->assertFileExists($file);

        if (is_file($file)) {
            $array = $this->readAsArray($file, Excel::XLSX);

            $firstRow = collect($array)->first();
            $this->assertEquals(['test', 'test'], $firstRow);

            $this->assertEquals([
                ['test', 'test'],
                ['test2', 'test2'],
            ], collect($array)->values()->all());
        }
    }
}
