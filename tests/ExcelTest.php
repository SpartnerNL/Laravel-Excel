<?php

namespace Maatwebsite\Excel\Tests;

use Maatwebsite\Excel\Excel;
use PHPUnit\Framework\Assert;
use Maatwebsite\Excel\Importer;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Collection;
use Illuminate\Contracts\View\View;
use Maatwebsite\Excel\Concerns\ToArray;
use Maatwebsite\Excel\Concerns\FromView;
use Maatwebsite\Excel\Concerns\Exportable;
use Maatwebsite\Excel\Concerns\Importable;
use Maatwebsite\Excel\Concerns\WithEvents;
use Maatwebsite\Excel\Events\BeforeWriting;
use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Facades\Excel as ExcelFacade;
use Maatwebsite\Excel\Tests\Data\Stubs\EmptyExport;
use Maatwebsite\Excel\Concerns\WithCustomCsvSettings;
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
    public function can_store_an_export_object_on_real_path()
    {
        $export = new EmptyExport;

        $response = $this->SUT->store($export, __DIR__ . '/Data/Disks/Local/filename.xlsx');

        $this->assertTrue($response);
        $this->assertFileExists(__DIR__ . '/Data/Disks/Local/filename.xlsx');
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
    public function can_store_tsv_export_with_default_settings()
    {
        $export = new EmptyExport;

        $response = $this->SUT->store($export, 'filename.tsv');

        $this->assertTrue($response);
        $this->assertFileExists(__DIR__ . '/Data/Disks/Local/filename.tsv');
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
     * @expectedExceptionMessage Cannot use FromQuery, FromArray or FromCollection and FromView on the same sheet
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

    /**
     * @test
     */
    public function can_import_a_simple_xlsx_file_to_array()
    {
        $import = new class {
            use Importable;
        };

        $this->assertEquals([
            [
                ['test', 'test'],
                ['test', 'test'],
            ],
        ], $import->toArray('import.xlsx'));
    }

    /**
     * @test
     */
    public function can_import_a_simple_xlsx_file_to_collection()
    {
        $import = new class {
            use Importable;
        };

        $this->assertEquals(new Collection([
            new Collection([
                new Collection(['test', 'test']),
                new Collection(['test', 'test']),
            ]),
        ]), $import->toCollection('import.xlsx'));
    }

    /**
     * @test
     */
    public function can_import_a_simple_xlsx_file()
    {
        $import = new class implements ToArray {
            /**
             * @param array $array
             */
            public function array(array $array)
            {
                Assert::assertEquals([
                    ['test', 'test'],
                    ['test', 'test'],
                ], $array);
            }
        };

        $imported = $this->SUT->import($import, 'import.xlsx');

        $this->assertInstanceOf(Importer::class, $imported);
    }

    /**
     * @test
     */
    public function can_import_a_tsv_file()
    {
        $import = new class implements ToArray, WithCustomCsvSettings {
            /**
             * @param array $array
             */
            public function array(array $array)
            {
                Assert::assertEquals([
                    'tconst',
                    'titleType',
                    'primaryTitle',
                    'originalTitle',
                    'isAdult',
                    'startYear',
                    'endYear',
                    'runtimeMinutes',
                    'genres',
                ], $array[0]);
            }

            /**
             * @return array
             */
            public function getCsvSettings(): array
            {
                return [
                    'delimiter' => "\t",
                ];
            }
        };

        $imported = $this->SUT->import($import, 'import-titles.tsv');

        $this->assertInstanceOf(Importer::class, $imported);
    }

    /**
     * @test
     */
    public function can_chain_imports()
    {
        $import1 = new class implements ToArray {
            /**
             * @param array $array
             */
            public function array(array $array)
            {
                Assert::assertEquals([
                    ['test', 'test'],
                    ['test', 'test'],
                ], $array);
            }
        };

        $import2 = new class implements ToArray {
            /**
             * @param array $array
             */
            public function array(array $array)
            {
                Assert::assertEquals([
                    ['test', 'test'],
                    ['test', 'test'],
                ], $array);
            }
        };

        $imported = $this->SUT
            ->import($import1, 'import.xlsx')
            ->import($import2, 'import.xlsx');

        $this->assertInstanceOf(Importer::class, $imported);
    }

    /**
     * @test
     */
    public function can_import_a_simple_xlsx_file_from_uploaded_file()
    {
        $import = new class implements ToArray {
            /**
             * @param array $array
             */
            public function array(array $array)
            {
                Assert::assertEquals([
                    ['test', 'test'],
                    ['test', 'test'],
                ], $array);
            }
        };

        $this->SUT->import($import, $this->givenUploadedFile(__DIR__ . '/Data/Disks/Local/import.xlsx'));
    }

    /**
     * @test
     */
    public function can_import_a_simple_xlsx_file_from_real_path()
    {
        $import = new class implements ToArray {
            /**
             * @param array $array
             */
            public function array(array $array)
            {
                Assert::assertEquals([
                    ['test', 'test'],
                    ['test', 'test'],
                ], $array);
            }
        };

        $this->SUT->import($import, __DIR__ . '/Data/Disks/Local/import.xlsx');
    }

    /**
     * @test
     * @expectedException \Maatwebsite\Excel\Exceptions\NoTypeDetectedException
     */
    public function import_will_throw_error_when_no_reader_type_could_be_detected_when_no_extension()
    {
        $import = new class implements ToArray {
            /**
             * @param array $array
             */
            public function array(array $array)
            {
                Assert::assertEquals([
                    ['test', 'test'],
                    ['test', 'test'],
                ], $array);
            }
        };

        $this->SUT->import($import, UploadedFile::fake()->create('import'));
    }

    /**
     * @test
     * @expectedException \Maatwebsite\Excel\Exceptions\NoTypeDetectedException
     */
    public function import_will_throw_error_when_no_reader_type_could_be_detected_with_unknown_extension()
    {
        $import = new class implements ToArray {
            /**
             * @param array $array
             */
            public function array(array $array)
            {
                //
            }
        };

        $this->SUT->import($import, 'unknown-reader-type.zip');
    }

    /**
     * @test
     */
    public function can_import_without_extension_with_explicit_reader_type()
    {
        $import = new class implements ToArray {
            /**
             * @param array $array
             */
            public function array(array $array)
            {
                Assert::assertEquals([
                    ['test', 'test'],
                    ['test', 'test'],
                ], $array);
            }
        };

        $this->SUT->import(
            $import,
            $this->givenUploadedFile(__DIR__ . '/Data/Disks/Local/import.xlsx', 'import'),
            null,
            Excel::XLSX
        );
    }
}
