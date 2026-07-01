<?php

namespace Maatwebsite\Excel\Tests;

use Illuminate\Contracts\View\View;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Collection;
use Maatwebsite\Excel\Concerns\Exportable;
use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\FromView;
use Maatwebsite\Excel\Concerns\Importable;
use Maatwebsite\Excel\Concerns\RegistersEventListeners;
use Maatwebsite\Excel\Concerns\ToArray;
use Maatwebsite\Excel\Concerns\WithCustomCsvSettings;
use Maatwebsite\Excel\Concerns\WithEvents;
use Maatwebsite\Excel\Excel;
use Maatwebsite\Excel\Exceptions\ConcernConflictException;
use Maatwebsite\Excel\Exceptions\NoTypeDetectedException;
use Maatwebsite\Excel\Facades\Excel as ExcelFacade;
use Maatwebsite\Excel\Importer;
use Maatwebsite\Excel\Tests\Data\Stubs\EmptyExport;
use Maatwebsite\Excel\Tests\Helpers\FileHelper;
use PHPUnit\Framework\Assert;
use Symfony\Component\HttpFoundation\BinaryFileResponse;

class ExcelTest extends TestCase
{
    protected Excel $SUT;

    protected function setUp(): void
    {
        parent::setUp();

        $this->SUT = $this->app->make(Excel::class);
    }

    public function test_can_download_an_export_object_with_facade(): void
    {
        $export = new EmptyExport;

        $response = ExcelFacade::download($export, 'filename.xlsx');

        $this->assertInstanceOf(BinaryFileResponse::class, $response);
        $this->assertSame('attachment; filename=filename.xlsx', str_replace('"', '', $response->headers->get('Content-Disposition')));
    }

    public function test_can_download_an_export_object(): void
    {
        $export = new EmptyExport;

        $response = $this->SUT->download($export, 'filename.xlsx');

        $this->assertInstanceOf(BinaryFileResponse::class, $response);
        $this->assertSame('attachment; filename=filename.xlsx', str_replace('"', '', $response->headers->get('Content-Disposition')));
    }

    public function test_can_store_an_export_object_on_default_disk(): void
    {
        $export = new EmptyExport;
        $name   = 'filename.xlsx';
        $path   = FileHelper::absolutePath($name, 'local');

        @unlink($path);

        $this->assertFileMissing($path);

        $response = $this->SUT->store($export, $name);

        $this->assertTrue($response);
        $this->assertFileExists($path);
    }

    public function test_can_store_an_export_object_on_another_disk(): void
    {
        $export = new EmptyExport;
        $name   = 'filename.xlsx';
        $path   = FileHelper::absolutePath($name, 'test');

        @unlink($path);

        $this->assertFileMissing($path);

        $response = $this->SUT->store($export, $name, 'test');

        $this->assertTrue($response);
        $this->assertFileExists($path);
    }

    public function test_can_store_csv_export_with_default_settings(): void
    {
        $export = new EmptyExport;
        $name   = 'filename.csv';
        $path   = FileHelper::absolutePath($name, 'local');

        @unlink($path);

        $this->assertFileMissing($path);

        $response = $this->SUT->store($export, $name);

        $this->assertTrue($response);
        $this->assertFileExists($path);
    }

    public function test_can_get_raw_export_contents(): void
    {
        $export = new EmptyExport;

        $response = $this->SUT->raw($export, Excel::XLSX);

        $this->assertNotEmpty($response);
    }

    public function test_can_store_tsv_export_with_default_settings(): void
    {
        $export = new EmptyExport;
        $name   = 'filename.tsv';
        $path   = FileHelper::absolutePath($name, 'local');

        @unlink($path);

        $this->assertFileMissing($path);

        $response = $this->SUT->store($export, $name);

        $this->assertTrue($response);
        $this->assertFileExists($path);
    }

    public function test_can_store_csv_export_with_custom_settings(): void
    {
        $export = new class implements FromCollection, WithCustomCsvSettings, WithEvents
        {
            use RegistersEventListeners;

            /**
             * @return Collection<int, array{string, string}>
             */
            public function collection(): Collection
            {
                return collect([
                    ['A1', 'B1'],
                    ['A2', 'B2'],
                ]);
            }

            /**
             * @return array<string, mixed>
             */
            public function getCsvSettings(): array
            {
                return [
                    'line_ending'            => PHP_EOL,
                    'enclosure'              => '"',
                    'delimiter'              => ';',
                    'include_separator_line' => true,
                    'excel_compatibility'    => false,
                ];
            }
        };

        $this->SUT->store($export, 'filename.csv');

        $contents = file_get_contents(__DIR__ . '/Data/Disks/Local/filename.csv');

        $this->assertStringContains('sep=;', $contents);
        $this->assertStringContains('"A1";"B1"', $contents);
        $this->assertStringContains('"A2";"B2"', $contents);
    }

    public function test_cannot_use_from_collection_and_from_view_on_same_export(): void
    {
        $this->expectException(ConcernConflictException::class);
        $this->expectExceptionMessage('Cannot use FromQuery, FromScout, FromArray or FromCollection and FromView on the same sheet.');

        $export = new class implements FromCollection, FromView
        {
            use Exportable;

            /**
             * @return Collection<int, mixed>
             */
            public function collection(): Collection
            {
                return collect();
            }

            public function view(): View
            {
                return view('users');
            }
        };

        $export->download('filename.csv');
    }

    public function test_can_import_a_simple_xlsx_file_to_array(): void
    {
        $import = new class
        {
            use Importable;
        };

        $this->assertSame([
            [
                ['test', 'test'],
                ['test', 'test'],
            ],
        ], $import->toArray('import.xlsx'));
    }

    public function test_can_import_a_simple_xlsx_file_to_collection(): void
    {
        $import = new class
        {
            use Importable;
        };

        $this->assertEquals(new Collection([
            new Collection([
                new Collection(['test', 'test']),
                new Collection(['test', 'test']),
            ]),
        ]), $import->toCollection('import.xlsx'));
    }

    public function test_can_import_a_simple_xlsx_file_to_collection_without_import_object(): void
    {
        $this->assertEquals(new Collection([
            new Collection([
                new Collection(['test', 'test']),
                new Collection(['test', 'test']),
            ]),
        ]), ExcelFacade::toCollection(null, 'import.xlsx'));
    }

    public function test_can_import_a_simple_xlsx_file(): void
    {
        $import = new class implements ToArray
        {
            public function array(array $array): void
            {
                Assert::assertSame([
                    ['test', 'test'],
                    ['test', 'test'],
                ], $array);
            }
        };

        $imported = $this->SUT->import($import, 'import.xlsx');

        $this->assertInstanceOf(Importer::class, $imported);
    }

    public function test_can_import_a_tsv_file(): void
    {
        $import = new class implements ToArray, WithCustomCsvSettings
        {
            public function array(array $array): void
            {
                Assert::assertSame([
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

    public function test_can_chain_imports(): void
    {
        $import1 = new class implements ToArray
        {
            public function array(array $array): void
            {
                Assert::assertSame([
                    ['test', 'test'],
                    ['test', 'test'],
                ], $array);
            }
        };

        $import2 = new class implements ToArray
        {
            public function array(array $array): void
            {
                Assert::assertSame([
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

    public function test_can_import_a_simple_xlsx_file_from_uploaded_file(): void
    {
        $import = new class implements ToArray
        {
            public function array(array $array): void
            {
                Assert::assertSame([
                    ['test', 'test'],
                    ['test', 'test'],
                ], $array);
            }
        };

        $this->SUT->import($import, $this->givenUploadedFile(__DIR__ . '/Data/Disks/Local/import.xlsx'));
    }

    public function test_can_import_a_simple_xlsx_file_from_real_path(): void
    {
        $import = new class implements ToArray
        {
            public function array(array $array): void
            {
                Assert::assertSame([
                    ['test', 'test'],
                    ['test', 'test'],
                ], $array);
            }
        };

        $this->SUT->import($import, __DIR__ . '/Data/Disks/Local/import.xlsx');
    }

    public function test_import_will_throw_error_when_no_reader_type_could_be_detected_when_no_extension(): void
    {
        $this->expectException(NoTypeDetectedException::class);

        $import = new class implements ToArray
        {
            public function array(array $array): void
            {
                Assert::assertSame([
                    ['test', 'test'],
                    ['test', 'test'],
                ], $array);
            }
        };

        $this->SUT->import($import, UploadedFile::fake()->create('import'));
    }

    public function test_import_will_throw_error_when_no_reader_type_could_be_detected_with_unknown_extension(): void
    {
        $this->expectException(NoTypeDetectedException::class);

        $import = new class implements ToArray
        {
            public function array(array $array): void
            {
                //
            }
        };

        $this->SUT->import($import, 'unknown-reader-type.zip');
    }

    public function test_can_import_without_extension_with_explicit_reader_type(): void
    {
        $import = new class implements ToArray
        {
            public function array(array $array): void
            {
                Assert::assertSame([
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
