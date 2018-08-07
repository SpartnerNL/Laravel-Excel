<?php

namespace Maatwebsite\Excel\Tests;

use PhpOffice\PhpSpreadsheet\IOFactory;
use Maatwebsite\Excel\ExcelServiceProvider;
use Orchestra\Testbench\TestCase as OrchestraTestCase;

class TestCase extends OrchestraTestCase
{
    /**
     * @param string $filePath
     * @param string $writerType
     *
     * @throws \PhpOffice\PhpSpreadsheet\Reader\Exception
     * @return \PhpOffice\PhpSpreadsheet\Spreadsheet
     */
    public function read(string $filePath, string $writerType)
    {
        $reader = IOFactory::createReader($writerType);

        return $reader->load($filePath);
    }

    /**
     * @param string   $filePath
     * @param string   $writerType
     * @param int|null $sheetIndex
     *
     * @throws \PhpOffice\PhpSpreadsheet\Exception
     * @return array
     */
    protected function readAsArray(string $filePath, string $writerType, int $sheetIndex = null)
    {
        $spreadsheet = $this->read($filePath, $writerType);

        if (null === $sheetIndex) {
            $sheet = $spreadsheet->getActiveSheet();
        } else {
            $sheet = $spreadsheet->getSheet($sheetIndex);
        }

        return $sheet->toArray();
    }

    /**
     * @param \Illuminate\Foundation\Application $app
     *
     * @return array
     */
    protected function getPackageProviders($app)
    {
        return [ExcelServiceProvider::class];
    }

    /**
     * @param \Illuminate\Foundation\Application $app
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
}
