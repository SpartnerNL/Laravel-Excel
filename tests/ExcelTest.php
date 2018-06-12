<?php

namespace Maatwebsite\Excel\Tests;

use Maatwebsite\Excel\Excel;
use Illuminate\Support\Collection;
use Illuminate\Contracts\View\View;
use Maatwebsite\Excel\Concerns\FromView;
use Maatwebsite\Excel\Concerns\Exportable;
use Maatwebsite\Excel\Concerns\WithEvents;
use Maatwebsite\Excel\Events\BeforeWriting;
use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Facades\Excel as ExcelFacade;
use Maatwebsite\Excel\Tests\Data\Stubs\EmptyExport;
use Maatwebsite\Excel\Concerns\RegistersEventListeners;
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
    public function can_download_an_export_object_with_facade()
    {
        $export = new EmptyExport();

        $response = ExcelFacade::download($export, 'filename.xlsx');

        $this->assertInstanceOf(BinaryFileResponse::class, $response);
        $this->assertEquals('attachment; filename=filename.xlsx', str_replace('"', '', $response->headers->get('Content-Disposition')));
    }

    /**
     * @test
     */
    public function can_download_an_export_object()
    {
        $export = new EmptyExport();

        $response = $this->SUT->download($export, 'filename.xlsx');

        $this->assertInstanceOf(BinaryFileResponse::class, $response);
        $this->assertEquals('attachment; filename=filename.xlsx', str_replace('"', '', $response->headers->get('Content-Disposition')));
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
        $export = new class implements WithEvents, FromCollection {
            use RegistersEventListeners;

            /**
             * @return Collection
             */
            public function collection()
            {
                return collect([
                    ['A1', 'B1'],
                    ['A2', 'B2'],
                ]);
            }

            /**
             * @param BeforeWriting $event
             */
            public static function beforeWriting(BeforeWriting $event)
            {
                $event->writer->setLineEnding(PHP_EOL);
                $event->writer->setEnclosure('');
                $event->writer->setDelimiter(';');
                $event->writer->setIncludeSeparatorLine(true);
                $event->writer->setExcelCompatibility(false);
            }
        };

        $this->SUT->store($export, 'filename.csv');

        $contents = file_get_contents(__DIR__ . '/Data/Disks/Local/filename.csv');

        $this->assertContains('sep=;', $contents);
        $this->assertContains('A1;B1', $contents);
        $this->assertContains('A2;B2', $contents);
    }

    /**
     * @test
     * @expectedException \Maatwebsite\Excel\Exceptions\ConcernConflictException
     * @expectedExceptionMessage Cannot use FromQuery or FromCollection and FromView on the same sheet
     */
    public function cannot_use_from_collection_and_from_view_on_same_export()
    {
        $export = new class implements FromCollection, FromView {
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
