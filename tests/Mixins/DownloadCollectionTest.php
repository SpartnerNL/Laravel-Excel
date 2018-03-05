<?php

namespace Maatwebsite\Excel\Tests\Mixins;

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
}
