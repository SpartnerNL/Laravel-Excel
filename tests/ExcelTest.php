<?php

namespace Maatwebsite\Excel\Tests;

use Illuminate\Contracts\View\View;
use Illuminate\Support\Collection;
use Maatwebsite\Excel\Concerns\Exportable;
use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\FromQuery;
use Maatwebsite\Excel\Concerns\FromView;
use Maatwebsite\Excel\Concerns\RegistersEventListeners;
use Maatwebsite\Excel\Concerns\WithEvents;
use Maatwebsite\Excel\Events\BeforeWriting;
use Maatwebsite\Excel\Excel;
use Maatwebsite\Excel\Tests\Data\Stubs\EmptyExport;
use Symfony\Component\HttpFoundation\BinaryFileResponse;

class ExcelTest extends TestCase
{
    /**
     * @var Excel
     */
    protected $SUT;

    public function setUp()
    {
        parent::setUp();

        $this->SUT = $this->app->make(Excel::class);
    }

    /**
     * @test
     */
    public function can_download_an_export_object()
    {
        $export = new EmptyExport();

        $response = $this->SUT->download($export, 'filename.xlsx');

        $this->assertInstanceOf(BinaryFileResponse::class, $response);
        $this->assertEquals('attachment; filename="filename.xlsx"', $response->headers->get('Content-Disposition'));
    }

    /**
     * @test
     */
    public function can_store_an_export_object_on_default_disk()
    {
        $export = new EmptyExport;

        $response = $this->SUT->store($export, 'filename.xlsx');

        $this->assertTrue($response);
        $this->assertFileExists(__DIR__ . '/Data/Disks/Local/filename.xlsx');
    }

    /**
     * @test
     */
    public function can_store_an_export_object_on_another_disk()
    {
        $export = new EmptyExport;

        $response = $this->SUT->store($export, 'filename.xlsx', 'test');

        $this->assertTrue($response);
        $this->assertFileExists(__DIR__ . '/Data/Disks/Test/filename.xlsx');
    }

    /**
     * @test
     */
    public function can_store_csv_export_with_default_settings()
    {
        $export = new EmptyExport;

        $response = $this->SUT->store($export, 'filename.csv');

        $this->assertTrue($response);
        $this->assertFileExists(__DIR__ . '/Data/Disks/Local/filename.csv');
    }

    /**
     * @test
     */
    public function can_store_csv_export_with_custom_settings()
    {
        $export = new class implements WithEvents, FromCollection
        {
            use RegistersEventListeners;

            /**
             * @return Collection
             */
            public function collection()
            {
                return collect([
                    ['A1', 'B1'],
                    ['A2', 'B2']
                ]);
            }

            public static function beforeWriting(BeforeWriting $event)
            {
                $event->writer->setLineEnding("\n");
                $event->writer->setEnclosure('');
                $event->writer->setDelimiter(';');
                $event->writer->setIncludeSeparatorLine(true);
                $event->writer->setExcelCompatibility(true);
            }
        };

        $this->SUT->store($export, 'filename.csv');

        $this->assertFileEquals(
            __DIR__ . '/Data/Stubs/Files/expected-csv.csv',
            __DIR__ . '/Data/Disks/Local/filename.csv'
        );
    }

    /**
     * @test
     * @expectedException \LogicException
     * @expectedExceptionMessage Cannot use FromQuery or FromCollection and FromView on the same sheet
     */
    public function cannot_use_from_collection_and_from_view_on_same_export()
    {
        $export = new class implements FromCollection, FromView
        {
            use Exportable;

            /**
             * @return Collection
             */
            public function collection()
            {
                return collect();
            }

            /**
             * @return View
             */
            public function view(): View
            {
                return view('users');
            }
        };

        $export->download('filename.csv');
    }
}
