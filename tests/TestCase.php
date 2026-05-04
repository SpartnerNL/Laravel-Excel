<?php

namespace Maatwebsite\Excel\Tests;

use Illuminate\Contracts\Queue\Job;
use Illuminate\Foundation\Application;
use Illuminate\Http\Testing\File;
use Maatwebsite\Excel\ExcelServiceProvider;
use Orchestra\Testbench\TestCase as OrchestraTestCase;
use PhpOffice\PhpSpreadsheet\Exception;
use PhpOffice\PhpSpreadsheet\IOFactory;
use PhpOffice\PhpSpreadsheet\Spreadsheet;
use PHPUnit\Framework\Constraint\StringContains;

class TestCase extends OrchestraTestCase
{
    /**
     * @return Spreadsheet
     *
     * @throws \PhpOffice\PhpSpreadsheet\Reader\Exception
     */
    public function read(string $filePath, string $writerType)
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
     * @return array
     *
     * @throws Exception
     */
    protected function readAsArray(string $filePath, string $writerType, ?int $sheetIndex = null)
    {
        $spreadsheet = $this->read($filePath, $writerType);

        if ($sheetIndex === null) {
            $sheet = $spreadsheet->getActiveSheet();
        } else {
            $sheet = $spreadsheet->getSheet($sheetIndex);
        }

        return $sheet->toArray();
    }

    /**
     * @param  Application  $app
     * @return array
     */
    protected function getPackageProviders($app)
    {
        return [
            ExcelServiceProvider::class,
        ];
    }

    /**
     * @param  Application  $app
     */
    protected function getEnvironmentSetUp($app)
    {
        $app['config']->set('filesystems.disks.local.root', __DIR__ . '/Data/Disks/Local');
        $app['config']->set('filesystems.disks.test', [
            'driver' => 'local',
            'root'   => __DIR__ . '/Data/Disks/Test',
        ]);

        $app['config']->set('database.default', 'testing');
        $app['config']->set('database.connections.testing', [
            'driver'   => 'mysql',
            'host'     => env('DB_HOST'),
            'port'     => env('DB_PORT'),
            'database' => env('DB_DATABASE'),
            'username' => env('DB_USERNAME'),
            'password' => env('DB_PASSWORD'),
        ]);

        $app['config']->set('view.paths', [
            __DIR__ . '/Data/Stubs/Views',
        ]);
    }

    /**
     * @return mixed
     */
    protected function inspectJobProperty(Job $job, string $property)
    {
        $dict  = (array) unserialize($job->payload()['data']['command']);
        $class = $job->resolveName();

        return $dict[$property] ?? $dict["\0*\0$property"] ?? $dict["\0$class\0$property"];
    }

    protected function assertStringContains(string $needle, string $haystack, string $message = '')
    {
        if (method_exists($this, 'assertStringContainsString')) {
            $this->assertStringContainsString($needle, $haystack, $message);
        } else {
            static::assertThat($haystack, new StringContains($needle, false), $message);
        }
    }

    protected function assertFileMissing(string $path)
    {
        if (method_exists($this, 'assertFileDoesNotExist')) {
            $this->assertFileDoesNotExist($path);
        } else {
            $this->assertFileNotExists($path);
        }
    }

    protected function assertRegex(string $pattern, string $string)
    {
        if (method_exists($this, 'assertMatchesRegularExpression')) {
            $this->assertMatchesRegularExpression($pattern, $string);
        } else {
            $this->assertRegExp($pattern, $string);
        }
    }
}
