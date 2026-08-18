<?php

declare(strict_types=1);

namespace Maatwebsite\Excel\Tests;

use Illuminate\Contracts\View\View;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Collection;
use Maatwebsite\Excel\Concerns\Exportable;
use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\FromView;
use Maatwebsite\Excel\Concerns\Import;
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
use Throwable;

final class ExcelTest extends TestCase
{
    protected readonly Excel $SUT;

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

    public function test_store_resolves_a_relative_path_within_the_disk(): void
    {
        $elsewhere = __DIR__ . '/Data/Disks/elsewhere.csv';
        $root      = config('filesystems.disks.local.root');

        file_put_contents($elsewhere, 'original contents');

        // Paths are resolved against the disk, never against the working directory.
        $relative = ltrim(str_replace(getcwd(), '', $root), DIRECTORY_SEPARATOR) . '/../elsewhere.csv';

        foreach ([$relative, '../elsewhere.csv'] as $path) {
            try {
                $this->SUT->store(new EmptyExport, $path, 'local');
            } catch (Throwable) {
                // The disk rejects paths it cannot resolve.
            }

            $this->assertSame('original contents', file_get_contents($elsewhere), 'Expected [' . $path . '] to resolve within the disk.');
        }

        @unlink($elsewhere);

        // Clean up what the relative path created inside the disk.
        FileHelper::recursiveDelete($root . DIRECTORY_SEPARATOR . explode(DIRECTORY_SEPARATOR, $relative)[0]);
    }

    public function test_store_resolves_an_absolute_path_within_the_disk(): void
    {
        $elsewhere = __DIR__ . '/Data/Disks/elsewhere.csv';
        $root      = config('filesystems.disks.local.root');

        file_put_contents($elsewhere, 'original contents');

        try {
            $this->SUT->store(new EmptyExport, $elsewhere, 'local');
        } catch (Throwable) {
            // The disk rejects paths it cannot resolve.
        }

        $this->assertSame('original contents', file_get_contents($elsewhere));

        @unlink($elsewhere);

        // Clean up what the absolute path created inside the disk.
        FileHelper::recursiveDelete(
            $root . DIRECTORY_SEPARATOR . explode(DIRECTORY_SEPARATOR, ltrim($elsewhere, DIRECTORY_SEPARATOR))[0]
        );
    }

    public function test_import_resolves_a_relative_path_on_the_disk_before_the_working_directory(): void
    {
        $name   = 'shadowed.csv';
        $onDisk = FileHelper::absolutePath($name, 'local');
        $inCwd  = getcwd() . DIRECTORY_SEPARATOR . $name;

        file_put_contents($onDisk, "from-the-disk\n");
        file_put_contents($inCwd, "from-the-working-directory\n");

        // The working directory may never shadow a file that lives on the disk.
        $this->assertSame([[['from-the-disk']]], $this->SUT->toArray($this->givenCsvImport(), $name, null, Excel::CSV));

        @unlink($onDisk);
        @unlink($inCwd);
    }

    public function test_import_reads_a_relative_path_from_the_working_directory_when_the_disk_does_not_have_it(): void
    {
        $name  = 'only-in-cwd.csv';
        $inCwd = getcwd() . DIRECTORY_SEPARATOR . $name;

        file_put_contents($inCwd, "from-the-working-directory\n");

        $this->assertSame(
            [[['from-the-working-directory']]],
            $this->SUT->toArray($this->givenCsvImport(), $name, null, Excel::CSV)
        );

        @unlink($inCwd);
    }

    public function test_import_reads_an_absolute_local_path_when_no_disk_is_given(): void
    {
        $elsewhere = __DIR__ . '/Data/Disks/absolute-import.csv';

        file_put_contents($elsewhere, "from-an-absolute-path\n");

        $this->assertSame(
            [[['from-an-absolute-path']]],
            $this->SUT->toArray($this->givenCsvImport(), $elsewhere, null, Excel::CSV)
        );

        @unlink($elsewhere);
    }

    public function test_import_resolves_paths_within_the_disk_when_a_disk_is_given(): void
    {
        $elsewhere = __DIR__ . '/Data/Disks/outside-the-disk.csv';

        file_put_contents($elsewhere, "outside-the-disk\n");

        // Paths are resolved against the disk, never against the working directory.
        foreach ([$elsewhere, '../outside-the-disk.csv'] as $path) {
            $imported = null;

            try {
                $imported = $this->SUT->toArray($this->givenCsvImport(), $path, 'local', Excel::CSV);
            } catch (Throwable) {
                // The disk rejects paths it cannot resolve.
            }

            $this->assertNotSame([[['outside-the-disk']]], $imported, 'Expected [' . $path . '] to resolve within the disk.');
        }

        @unlink($elsewhere);
    }

    public function test_store_cleans_up_the_temporary_file_when_the_disk_fails(): void
    {
        $temporaryPath = FileHelper::absolutePath('temporary-files', 'local');
        FileHelper::recursiveDelete($temporaryPath);

        config()->set('excel.temporary_files.local_path', $temporaryPath);

        $failed = false;

        try {
            // The Excel instance is rebuilt so it picks up the temporary path above.
            $this->app->make(Excel::class)->store(new EmptyExport, 'filename.xlsx', 'non-existing-disk');
        } catch (Throwable) {
            $failed = true;
        }

        $this->assertTrue($failed, 'Storing on a non existing disk should not succeed.');
        $this->assertSame([], glob($temporaryPath . DIRECTORY_SEPARATOR . '*'), 'The temporary file was not cleaned up.');

        FileHelper::recursiveDelete($temporaryPath);
    }

    public function test_storing_over_an_existing_file_does_not_leave_leftover_contents(): void
    {
        $name = 'filename.csv';
        $path = FileHelper::absolutePath($name, 'local');

        @unlink($path);

        $this->SUT->store($this->givenCsvExport([['AAAAAAAAAA', 'BBBBBBBBBB']]), $name);
        $this->SUT->store($this->givenCsvExport([['A', 'B']]), $name);

        $contents = file_get_contents($path);

        $this->assertStringContains('"A","B"', $contents);
        $this->assertStringNotContainsString('AAAAAAAAAA', (string) $contents, 'Leftovers of the previous export were not truncated.');
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
        $import = new class implements Import
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
        $import = new class implements Import
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

    private function givenCsvImport(): ToArray
    {
        return new class implements ToArray
        {
            /**
             * @param  array<array-key, mixed>  $array
             */
            public function array(array $array): void
            {
            }
        };
    }

    /**
     * @param  array<int, array<int, mixed>>  $rows
     * @return FromCollection<int, mixed>
     */
    private function givenCsvExport(array $rows): FromCollection
    {
        return new readonly class($rows) implements FromCollection
        {
            /**
             * @param  array<int, array<int, mixed>>  $rows
             */
            public function __construct(private array $rows)
            {
            }

            /**
             * @return Collection<int, mixed>
             */
            public function collection(): Collection
            {
                return collect($this->rows);
            }
        };
    }
}
