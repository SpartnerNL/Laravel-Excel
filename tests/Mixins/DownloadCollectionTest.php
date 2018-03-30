<?php

namespace Maatwebsite\Excel\Tests\Mixins;

use Maatwebsite\Excel\Excel;
use Illuminate\Support\Collection;
use Maatwebsite\Excel\Tests\TestCase;
use Symfony\Component\HttpFoundation\BinaryFileResponse;

class DownloadCollectionTest extends TestCase
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

        $response = $collection->downloadExcel('collection-download.xlsx');

        $this->assertInstanceOf(BinaryFileResponse::class, $response);
        $this->assertEquals(
            'attachment; filename="collection-download.xlsx"',
            $response->headers->get('Content-Disposition')
        );
    }

    /**
     * @test
     */
    public function can_store_a_collection_with_headers_as_excel()
    {
        $collection = new Collection([
            ['column_1' => 'test', 'column_2' => 'test'],
            ['column_1' => 'test', 'column_2' => 'test'],
        ]);

        $response = $collection->downloadExcel('collection-headers-download.xlsx', Excel::XLSX, true);

        $array = $this->readAsArray($response->getFile()->getPathName(), Excel::XLSX);

        $this->assertEquals($collection->collapse()->keys()->all(), collect($array)->first());
        $this->assertInstanceOf(BinaryFileResponse::class, $response);
        $this->assertEquals(
            'attachment; filename="collection-headers-download.xlsx"',
            $response->headers->get('Content-Disposition')
        );
    }
}
