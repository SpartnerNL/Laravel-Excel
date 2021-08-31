<?php

namespace Maatwebsite\Excel\Tests;

use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Foundation\Bus\PendingDispatch;
use Illuminate\Support\Collection;
use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\ToModel;
use Maatwebsite\Excel\Facades\Excel as ExcelFacade;
use Maatwebsite\Excel\Fakes\ExcelFake;
use Maatwebsite\Excel\Tests\Data\Stubs\ChainedJobStub;
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
        ExcelFacade::matchByRegex();
        ExcelFacade::assertDownloaded('/\w{10}-\w{8}\.csv/');
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
        ExcelFacade::matchByRegex();
        ExcelFacade::assertStored('/\w{6}-\w{8}\.csv/', 's3');
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
        ExcelFacade::matchByRegex();
        ExcelFacade::assertStored('/\w{6}-\w{8}\.csv/');
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
        ExcelFacade::matchByRegex();
        ExcelFacade::assertQueued('/\w{6}-\w{8}\.csv/', 's3');
    }

    /**
     * @test
     */
    public function can_assert_against_a_fake_implicitly_queued_export()
    {
        ExcelFacade::fake();

        $response = ExcelFacade::store($this->givenQueuedExport(), 'queued-filename.csv', 's3');

        $this->assertInstanceOf(PendingDispatch::class, $response);

        ExcelFacade::assertStored('queued-filename.csv', 's3');
        ExcelFacade::assertQueued('queued-filename.csv', 's3');
        ExcelFacade::assertQueued('queued-filename.csv', 's3', function (FromCollection $export) {
            return $export->collection()->contains('foo');
        });
        ExcelFacade::matchByRegex();
        ExcelFacade::assertQueued('/\w{6}-\w{8}\.csv/', 's3');
    }

    /**
     * @test
     */
    public function can_assert_against_a_fake_queued_export_with_chain()
    {
        ExcelFacade::fake();

        ExcelFacade::queue(
            $this->givenQueuedExport(), 'queued-filename.csv', 's3'
        )->chain([
            new ChainedJobStub(),
        ]);

        ExcelFacade::assertQueuedWithChain([
            new ChainedJobStub(),
        ]);
    }

    /**
     * @test
     */
    public function can_assert_against_a_fake_raw_export()
    {
        ExcelFacade::fake();

        $response = ExcelFacade::raw($this->givenExport(), \Maatwebsite\Excel\Excel::XLSX);

        $this->assertIsString($response);

        ExcelFacade::assertExportedInRaw(get_class($this->givenExport()));
        ExcelFacade::assertExportedInRaw(get_class($this->givenExport()), function (FromCollection $export) {
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
        ExcelFacade::matchByRegex();
        ExcelFacade::assertImported('/\w{6}-\w{8}\.csv/', 's3');
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
        ExcelFacade::matchByRegex();
        ExcelFacade::assertImported('/\w{6}\.xlsx/');
    }

    /**
     * @test
     */
    public function can_assert_against_a_fake_queued_import()
    {
        ExcelFacade::fake();

        $response = ExcelFacade::queueImport($this->givenQueuedImport(), 'queued-filename.csv', 's3');

        $this->assertInstanceOf(PendingDispatch::class, $response);

        ExcelFacade::assertImported('queued-filename.csv', 's3');
        ExcelFacade::assertQueued('queued-filename.csv', 's3');
        ExcelFacade::assertQueued('queued-filename.csv', 's3', function (ToModel $import) {
            return $import->model([]) instanceof User;
        });
        ExcelFacade::matchByRegex();
        ExcelFacade::assertQueued('/\w{6}-\w{8}\.csv/', 's3');
    }

    /**
     * @test
     */
    public function can_assert_against_a_fake_implicitly_queued_import()
    {
        ExcelFacade::fake();

        $response = ExcelFacade::import($this->givenQueuedImport(), 'queued-filename.csv', 's3');

        $this->assertInstanceOf(PendingDispatch::class, $response);

        ExcelFacade::assertImported('queued-filename.csv', 's3');
        ExcelFacade::assertQueued('queued-filename.csv', 's3');
        ExcelFacade::assertQueued('queued-filename.csv', 's3', function (ToModel $import) {
            return $import->model([]) instanceof User;
        });
        ExcelFacade::matchByRegex();
        ExcelFacade::assertQueued('/\w{6}-\w{8}\.csv/', 's3');
    }

    /**
     * @test
     */
    public function can_assert_against_a_fake_queued_import_with_chain()
    {
        ExcelFacade::fake();

        ExcelFacade::queueImport(
            $this->givenQueuedImport(), 'queued-filename.csv', 's3'
        )->chain([
            new ChainedJobStub(),
        ]);

        ExcelFacade::assertQueuedWithChain([
            new ChainedJobStub(),
        ]);
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
        ExcelFacade::matchByRegex();
        ExcelFacade::assertQueued('/\w{6}-\w{8}\.csv/');
    }

    /**
     * @return FromCollection
     */
    private function givenExport()
    {
        return new class implements FromCollection
        {
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
     * @return FromCollection
     */
    private function givenQueuedExport()
    {
        return new class implements FromCollection, ShouldQueue
        {
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
        return new class implements ToModel
        {
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

    /**
     * @return object
     */
    private function givenQueuedImport()
    {
        return new class implements ToModel, ShouldQueue
        {
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
