<?php

declare(strict_types=1);

namespace Maatwebsite\Excel\Tests;

use Illuminate\Contracts\Queue\Job;
use Illuminate\Foundation\Application;
use Illuminate\Http\Testing\File;
use Maatwebsite\Excel\ExcelServiceProvider;
use Orchestra\Testbench\TestCase as OrchestraTestCase;
use PhpOffice\PhpSpreadsheet\Exception;
use PhpOffice\PhpSpreadsheet\IOFactory;
use PhpOffice\PhpSpreadsheet\Spreadsheet;

class TestCase extends OrchestraTestCase
{
    protected function setUp(): void
    {
        parent::setUp();

        // Number formatting (e.g. currency masks) is LC_NUMERIC aware, which would
        // make assertions depend on the LANG of the machine running the tests.
        setlocale(LC_NUMERIC, 'C');
    }

    /**
     * @throws \PhpOffice\PhpSpreadsheet\Reader\Exception
     */
    public function read(string $filePath, string $writerType): Spreadsheet
    {
        $reader = IOFactory::createReader($writerType);

        return $reader->load($filePath);
    }

    public function givenUploadedFile(string $filePath, ?string $filename = null): File
    {
        $filename ??= basename($filePath);

        // Create temporary file.
        $newFilePath = tempnam(sys_get_temp_dir(), 'import-');

        // Copy the existing file to a temporary file.
        copy($filePath, $newFilePath);

        return new File($filename, fopen($newFilePath, 'r'));
    }

    /**
     * @return array<array<mixed>>
     *
     * @throws Exception
     */
    protected function readAsArray(string $filePath, string $writerType, ?int $sheetIndex = null): array
    {
        $spreadsheet = $this->read($filePath, $writerType);

        $sheet = $sheetIndex === null ? $spreadsheet->getActiveSheet() : $spreadsheet->getSheet($sheetIndex);

        return $sheet->toArray();
    }

    /**
     * @param  Application  $app
     * @return list<class-string>
     */
    protected function getPackageProviders($app): array
    {
        return [
            ExcelServiceProvider::class,
            TestAppConfigProvider::class,
        ];
    }

    protected function inspectJobProperty(Job $job, string $property): mixed
    {
        $dict  = (array) unserialize($job->payload()['data']['command']);
        $class = $job->resolveName();

        return $dict[$property] ?? $dict["\0*\0$property"] ?? $dict["\0$class\0$property"];
    }

    protected function assertStringContains(string $needle, string $haystack, string $message = ''): void
    {
        $this->assertStringContainsString($needle, $haystack, $message);
    }

    protected function assertFileMissing(string $path): void
    {
        $this->assertFileDoesNotExist($path);
    }

    protected function assertRegex(string $pattern, string $string): void
    {
        $this->assertMatchesRegularExpression($pattern, $string);
    }
}
