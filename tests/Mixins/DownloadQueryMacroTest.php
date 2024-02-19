<?php

namespace Maatwebsite\Excel\Tests\Mixins;

use Maatwebsite\Excel\Excel;
use Maatwebsite\Excel\Tests\Data\Stubs\Database\User;
use Maatwebsite\Excel\Tests\TestCase;
use Symfony\Component\HttpFoundation\BinaryFileResponse;

class DownloadQueryMacroTest extends TestCase
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
        $response = User::downloadExcel('query-download.xlsx', Excel::XLSX);

        $array = $this->readAsArray($response->getFile()->getPathName(), Excel::XLSX);
        $this->assertCount(100, $array);

        $this->assertInstanceOf(BinaryFileResponse::class, $response);
        $this->assertEquals(
            'attachment; filename=query-download.xlsx',
            str_replace('"', '', $response->headers->get('Content-Disposition'))
        );
    }

    public function test_can_download_a_collection_with_headers_as_excel()
    {
        $response = User::downloadExcel('collection-headers-download.xlsx', Excel::XLSX, true);

        $array = $this->readAsArray($response->getFile()->getPathName(), Excel::XLSX);

        $this->assertCount(101, $array);

        $this->assertEquals(['id', 'name', 'email', 'remember_token', 'created_at', 'updated_at'], collect($array)->first());
    }
}
