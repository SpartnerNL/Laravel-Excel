<?php

namespace Maatwebsite\Excel\Tests;

use Illuminate\Support\Collection;
use Maatwebsite\Excel\Fakes\ExcelFake;
use Illuminate\Database\Eloquent\Model;
use Maatwebsite\Excel\Concerns\ToModel;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\PendingDispatch;
use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Facades\Excel as ExcelFacade;
use Maatwebsite\Excel\Tests\Data\Stubs\Database\User;
use Symfony\Component\HttpFoundation\BinaryFileResponse;

class ExcelFakeTest extends TestCase
{
    /**
     * @test
     */
    public function can_fake_an_export()
    {
        ExcelFacade::fake();

        // Excel instance should be swapped to the fake now.
        $this->assertInstanceOf(ExcelFake::class, $this->app->make('excel'));
    }

    /**
     * @test
     */
    public function can_assert_against_a_fake_downloaded_export()
    {
        ExcelFacade::fake();

        $response = ExcelFacade::download($this->givenExport(), 'downloaded-filename.csv');

        $this->assertInstanceOf(BinaryFileResponse::class, $response);

        ExcelFacade::assertDownloaded('downloaded-filename.csv');
        ExcelFacade::assertDownloaded('downloaded-filename.csv', function (FromCollection $export) {
            return $export->collection()->contains('foo');
        });
    }

    /**
     * @test
     */
    public function can_assert_against_a_fake_stored_export()
    {
        ExcelFacade::fake();

        $response = ExcelFacade::store($this->givenExport(), 'stored-filename.csv', 's3');

        $this->assertTrue($response);

        ExcelFacade::assertStored('stored-filename.csv', 's3');
        ExcelFacade::assertStored('stored-filename.csv', 's3', function (FromCollection $export) {
            return $export->collection()->contains('foo');
        });
    }

    /**
     * @test
     */
    public function a_callback_can_be_passed_as_the_second_argument_when_asserting_against_a_faked_stored_export()
    {
        ExcelFacade::fake();

        $response = ExcelFacade::store($this->givenExport(), 'stored-filename.csv');

        $this->assertTrue($response);

        ExcelFacade::assertStored('stored-filename.csv');
        ExcelFacade::assertStored('stored-filename.csv', function (FromCollection $export) {
            return $export->collection()->contains('foo');
        });
    }

    /**
     * @test
     */
    public function can_assert_against_a_fake_queued_export()
    {
        ExcelFacade::fake();

        $response = ExcelFacade::queue($this->givenExport(), 'queued-filename.csv', 's3');

        $this->assertInstanceOf(PendingDispatch::class, $response);

        ExcelFacade::assertQueued('queued-filename.csv', 's3');
        ExcelFacade::assertQueued('queued-filename.csv', 's3', function (FromCollection $export) {
            return $export->collection()->contains('foo');
        });
    }

    /**
     * @test
     */
    public function can_assert_against_a_fake_import()
    {
        ExcelFacade::fake();

        ExcelFacade::import($this->givenImport(), 'stored-filename.csv', 's3');

        ExcelFacade::assertImported('stored-filename.csv', 's3');
        ExcelFacade::assertImported('stored-filename.csv', 's3', function (ToModel $import) {
            return $import->model([]) instanceof User;
        });
    }

    /**
     * @test
     */
    public function can_assert_against_a_fake_import_with_uploaded_file()
    {
        ExcelFacade::fake();

        ExcelFacade::import($this->givenImport(), $this->givenUploadedFile(__DIR__ . '/Data/Disks/Local/import.xlsx'));

        ExcelFacade::assertImported('import.xlsx');
        ExcelFacade::assertImported('import.xlsx', function (ToModel $import) {
            return $import->model([]) instanceof User;
        });
    }

    /**
     * @test
     */
    public function can_assert_against_a_fake_queued_import()
    {
        ExcelFacade::fake();

        $response = ExcelFacade::queueImport($this->givenImport(), 'queued-filename.csv', 's3');

        $this->assertInstanceOf(PendingDispatch::class, $response);

        ExcelFacade::assertQueued('queued-filename.csv', 's3');
        ExcelFacade::assertQueued('queued-filename.csv', 's3', function (ToModel $import) {
            return $import->model([]) instanceof User;
        });
    }

    /**
     * @test
     */
    public function a_callback_can_be_passed_as_the_second_argument_when_asserting_against_a_faked_queued_export()
    {
        ExcelFacade::fake();

        $response = ExcelFacade::queue($this->givenExport(), 'queued-filename.csv');

        $this->assertInstanceOf(PendingDispatch::class, $response);

        ExcelFacade::assertQueued('queued-filename.csv');
        ExcelFacade::assertQueued('queued-filename.csv', function (FromCollection $export) {
            return $export->collection()->contains('foo');
        });
    }

    /**
     * @return FromCollection
     */
    private function givenExport()
    {
        return new class implements FromCollection {
            /**
             * @return Collection
             */
            public function collection()
            {
                return collect(['foo', 'bar']);
            }
        };
    }

    /**
     * @return object
     */
    private function givenImport()
    {
        return new class implements ToModel, ShouldQueue {
            /**
             * @param array $row
             *
             * @return Model|null
             */
            public function model(array $row)
            {
                return new User([]);
            }
        };
    }
}
