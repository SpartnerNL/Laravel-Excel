<?php

namespace Maatwebsite\Excel\Tests;

use Illuminate\Http\Testing\File;
use Illuminate\Contracts\Queue\Job;
use PhpOffice\PhpSpreadsheet\IOFactory;
use Maatwebsite\Excel\ExcelServiceProvider;
use Orchestra\Database\ConsoleServiceProvider;
use PHPUnit\Framework\Constraint\StringContains;
use Orchestra\Testbench\TestCase as OrchestraTestCase;
use Illuminate\Support\Facades\Storage;

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
     * @param string      $filePath
     * @param string|null $filename
     *
     * @return File
     */
    public function givenUploadedFile(string $filePath, string $filename = null): File
    {

        $filename = $filename ?? basename($filePath);

        // Create temporary file.
        $newFilePath = tempnam(sys_get_temp_dir(), 'import-');

        // Copy the existing file to a temporary file.
        copy($filePath, $newFilePath);

        return new File($filename, fopen($newFilePath, 'r'));
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
        return [
            ConsoleServiceProvider::class,
            ExcelServiceProvider::class,
        ];
    }

    /**
     * @param \Illuminate\Foundation\Application $app
     */
    protected function getEnvironmentSetUp($app)
    {
		// dd(storage_path());
		// dd(Storage::disk('local'));
		$app->useStoragePath(__DIR__ . '/Data/Disks/Local');
        $app['config']->set('filesystems.disks.local.root', __DIR__ . '/Data/Disks/Local');
        $app['config']->set('filesystems.disks.test', [
            'driver' => 'local',
            'root'   => __DIR__ . '/Data/Disks/Test',
        ]);
		// dd(storage_path());
		// dd(base_path());
		// dd(Storage::disk('local'));
		// dd(Storage::disk('test'));

		/*
		 * Original Option changing the driver and
		 * customizing the phpunit.xml
		 *
				Note:
				phpunit.xml <- Copied from phpuni.xml.dist 

			  <env name="DB_HOST" value="127.0.0.1"/>
			  <env name="DB_PORT" value="3306"/>
			  <env name="DB_DATABASE" value="D:\paso\Cotizador\dbExcelTest.sqlite"/>
			  <env name="DB_USERNAME" value="root"/>
			  <env name="DB_PASSWORD" value=""/>

		*/
        $app['config']->set('database.default', 'testing');
        $app['config']->set('database.connections.testing', [
            // 'driver'   => 'mysql',
            'driver'   => 'sqlite',
            'host'     => env('DB_HOST'),
            'port'     => env('DB_PORT'),
            'database' => env('DB_DATABASE'),
            'username' => env('DB_USERNAME'),
            'password' => env('DB_PASSWORD'),
        ]);

		/*
		 * Alternative Option suggested https://github.com/orchestral/testbench
		 * customizing the phpunit.xml
		 *
		*/
/*
		// Setup default database to use sqlite :memory:
		$app['config']->set('database.default', 'testbench');
		$app['config']->set('database.connections.testbench', [
			'driver'   => 'sqlite',
			'database' => ':memory:',
			'prefix'   => '',
		]);
*/
		/*
		 * Alternative Option suggested https://github.com/orchestral/testbench
		 * customizing the phpunit.xml
		 * Without Declaration in this section
				Note:
				phpunit.xml <- Copied from phpuni.xml.dist 

			<php>
			  <env name="APP_KEY" value="base64:6igsHe3RYC88h3Wje3VzSNqPwUr7Z5ru+NZw/9qwY5M=" />
			  <env name="DB_CONNECTION" value="testing"/>
			</php>

		*/

        $app['config']->set('view.paths', [
            __DIR__ . '/Data/Stubs/Views',
        ]);
    }

    /**
     * @param Job    $job
     * @param string $property
     *
     * @return mixed
     */
    protected function inspectJobProperty(Job $job, string $property)
    {
        $dict  = (array) unserialize($job->payload()['data']['command']);
        $class = $job->resolveName();

        return $dict[$property] ?? $dict["\0*\0$property"] ?? $dict["\0$class\0$property"];
    }

    /**
     * @param string $needle
     * @param string $haystack
     * @param string $message
     */
    protected function assertStringContains(string $needle, string $haystack, string $message = '')
    {
        if (method_exists($this, 'assertStringContainsString')) {
            $this->assertStringContainsString($needle, $haystack, $message);
        } else {
            static::assertThat($haystack, new StringContains($needle, false), $message);
        }
    }
}
