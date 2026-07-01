<?php

namespace Maatwebsite\Excel\Tests\Mixins;

use Illuminate\Support\Collection;
use Maatwebsite\Excel\Excel;
use Maatwebsite\Excel\Tests\Data\Stubs\Database\User;
use Maatwebsite\Excel\Tests\TestCase;
use Symfony\Component\HttpFoundation\BinaryFileResponse;

class DownloadCollectionTest extends TestCase
{
    public function test_can_download_a_collection_as_excel(): void
    {
        $collection = new Collection([
            ['column_1' => 'test', 'column_2' => 'test'],
            ['column_1' => 'test2', 'column_2' => 'test2'],
        ]);

        $response = $collection->downloadExcel('collection-download.xlsx', Excel::XLSX);

        $array = $this->readAsArray($response->getFile()->getPathName(), Excel::XLSX);

        // First row are not headings
        $firstRow = collect($array)->first();
        $this->assertSame(['test', 'test'], $firstRow);

        $this->assertInstanceOf(BinaryFileResponse::class, $response);
        $this->assertSame(
            'attachment; filename=collection-download.xlsx',
            str_replace('"', '', $response->headers->get('Content-Disposition'))
        );
    }

    public function test_can_download_a_collection_with_headers_as_excel(): void
    {
        $collection = new Collection([
            ['column_1' => 'test', 'column_2' => 'test'],
            ['column_1' => 'test', 'column_2' => 'test'],
        ]);

        $response = $collection->downloadExcel('collection-headers-download.xlsx', Excel::XLSX, true);

        $array = $this->readAsArray($response->getFile()->getPathName(), Excel::XLSX);

        $this->assertSame(['column_1', 'column_2'], collect($array)->first());
    }

    public function test_can_download_collection_with_headers_with_hidden_eloquent_attributes(): void
    {
        $collection = new Collection([
            new User(['name' => 'Patrick', 'password' => 'my_password']),
        ]);

        $response = $collection->downloadExcel('collection-headers-download.xlsx', Excel::XLSX, true);

        $array = $this->readAsArray($response->getFile()->getPathName(), Excel::XLSX);

        $this->assertSame(['name'], collect($array)->first());
    }

    public function test_can_download_collection_with_headers_when_making_attributes_visible(): void
    {
        $user = new User(['name' => 'Patrick', 'password' => 'my_password']);
        $user->makeVisible(['password']);

        $collection = new Collection([
            $user,
        ]);

        $response = $collection->downloadExcel('collection-headers-download.xlsx', Excel::XLSX, true);

        $array = $this->readAsArray($response->getFile()->getPathName(), Excel::XLSX);

        $this->assertSame(['name', 'password'], collect($array)->first());
    }

    public function test_can_set_custom_response_headers(): void
    {
        $collection = new Collection([
            ['column_1' => 'test', 'column_2' => 'test'],
            ['column_1' => 'test2', 'column_2' => 'test2'],
        ]);

        $responseHeaders = [
            'CUSTOMER-HEADER-1' => 'CUSTOMER-HEADER1-VAL',
            'CUSTOMER-HEADER-2' => 'CUSTOMER-HEADER2-VAL',
        ];
        $response = $collection->downloadExcel('collection-download.xlsx', Excel::XLSX, false, $responseHeaders);
        static::assertInstanceOf(BinaryFileResponse::class, $response);
        $this->assertTrue($response->headers->contains('CUSTOMER-HEADER-1', 'CUSTOMER-HEADER1-VAL'));
        $this->assertTrue($response->headers->contains('CUSTOMER-HEADER-2', 'CUSTOMER-HEADER2-VAL'));
    }
}
