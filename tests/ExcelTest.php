<?php

namespace Seoperin\LaravelExcel\Tests;

use Seoperin\LaravelExcel\Excel;
use PHPUnit\Framework\Assert;
use Seoperin\LaravelExcel\Importer;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Collection;
use Illuminate\Contracts\View\View;
use Seoperin\LaravelExcel\Concerns\ToArray;
use Seoperin\LaravelExcel\Concerns\FromView;
use Seoperin\LaravelExcel\Concerns\Exportable;
use Seoperin\LaravelExcel\Concerns\Importable;
use Seoperin\LaravelExcel\Concerns\WithEvents;
use Seoperin\LaravelExcel\Concerns\FromCollection;
use Seoperin\LaravelExcel\Facades\Excel as ExcelFacade;
use Seoperin\LaravelExcel\Tests\Data\Stubs\EmptyExport;
use Seoperin\LaravelExcel\Concerns\WithCustomCsvSettings;
use Seoperin\LaravelExcel\Concerns\RegistersEventListeners;
use Symfony\Component\HttpFoundation\BinaryFileResponse;

class ExcelTest extends TestCase
{
    /**
     * @var Excel
     */
    protected $SUT;

    protected function setUp(): void
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
    public function can_get_raw_export_contents()
    {
        $export = new EmptyExport;

        $response = $this->SUT->raw($export, Excel::XLSX);

        $this->assertNotEmpty($response);
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
        $export = new class implements WithEvents, FromCollection, WithCustomCsvSettings {
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
             * @return array
             */
            public function getCsvSettings(): array
            {
                return [
                    'line_ending'            => PHP_EOL,
                    'enclosure'              => '',
                    'delimiter'              => ';',
                    'include_separator_line' => true,
                    'excel_compatibility'    => false,
                ];
            }
        };

        $this->SUT->store($export, 'filename.csv');

        $contents = file_get_contents(__DIR__ . '/Data/Disks/Local/filename.csv');

        $this->assertStringContains('sep=;', $contents);
        $this->assertStringContains('A1;B1', $contents);
        $this->assertStringContains('A2;B2', $contents);
    }

    /**
     * @test
     */
    public function cannot_use_from_collection_and_from_view_on_same_export()
    {
        $this->expectException(\Seoperin\LaravelExcel\Exceptions\ConcernConflictException::class);
        $this->expectExceptionMessage('Cannot use FromQuery, FromArray or FromCollection and FromView on the same sheet');

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
     */
    public function import_will_throw_error_when_no_reader_type_could_be_detected_when_no_extension()
    {
        $this->expectException(\Seoperin\LaravelExcel\Exceptions\NoTypeDetectedException::class);

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
     */
    public function import_will_throw_error_when_no_reader_type_could_be_detected_with_unknown_extension()
    {
        $this->expectException(\Seoperin\LaravelExcel\Exceptions\NoTypeDetectedException::class);

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
