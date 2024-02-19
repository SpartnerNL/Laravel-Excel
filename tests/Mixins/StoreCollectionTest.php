<?php

namespace Maatwebsite\Excel\Tests\Mixins;

use Illuminate\Support\Collection;
use Maatwebsite\Excel\Excel;
use Maatwebsite\Excel\Tests\Data\Stubs\Database\User;
use Maatwebsite\Excel\Tests\TestCase;

class StoreCollectionTest extends TestCase
{
    public function test_can_store_a_collection_as_excel()
    {
        $collection = new Collection([
            ['test', 'test'],
            ['test', 'test'],
        ]);

        $response = $collection->storeExcel('collection-store.xlsx');

        $this->assertTrue($response);
        $this->assertFileExists(__DIR__ . '/../Data/Disks/Local/collection-store.xlsx');
    }

    public function test_can_store_a_collection_as_excel_on_non_default_disk()
    {
        $collection = new Collection([
            ['column_1' => 'test', 'column_2' => 'test'],
            ['column_1' => 'test2', 'column_2' => 'test2'],
        ]);

        $response = $collection->storeExcel('collection-store.xlsx', null, Excel::XLSX);

        $file = __DIR__ . '/../Data/Disks/Local/collection-store.xlsx';

        $this->assertTrue($response);
        $this->assertFileExists($file);

        $array = $this->readAsArray($file, Excel::XLSX);

        // First row are not headings
        $firstRow = collect($array)->first();
        $this->assertEquals(['test', 'test'], $firstRow);

        $this->assertEquals([
            ['test', 'test'],
            ['test2', 'test2'],
        ], collect($array)->values()->all());
    }

    public function test_can_store_a_collection_with_headings_as_excel()
    {
        $collection = new Collection([
            ['column_1' => 'test', 'column_2' => 'test'],
            ['column_1' => 'test', 'column_2' => 'test'],
        ]);

        $response = $collection->storeExcel('collection-headers-store.xlsx', null, Excel::XLSX, true);

        $file = __DIR__ . '/../Data/Disks/Local/collection-headers-store.xlsx';

        $this->assertTrue($response);
        $this->assertFileExists($file);

        $array = $this->readAsArray($file, Excel::XLSX);
        $this->assertEquals(['column_1', 'column_2'], collect($array)->first());

        $this->assertEquals([
            ['test', 'test'],
            ['test', 'test'],
        ], collect($array)->except(0)->values()->all());
    }

    public function test_can_store_a_model_collection_with_headings_as_excel()
    {
        $this->withFactories(__DIR__ . '/../Data/Stubs/Database/Factories');

        $collection = factory(User::class, 2)->make();

        $response = $collection->storeExcel('model-collection-headers-store.xlsx', null, Excel::XLSX, true);

        $file = __DIR__ . '/../Data/Disks/Local/model-collection-headers-store.xlsx';

        $this->assertTrue($response);
        $this->assertFileExists($file);

        $array = $this->readAsArray($file, Excel::XLSX);
        $this->assertEquals(['name', 'email', 'remember_token'], collect($array)->first());
    }
}
