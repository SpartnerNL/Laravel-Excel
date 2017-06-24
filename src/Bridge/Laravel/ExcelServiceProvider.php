<?php

namespace Maatwebsite\Excel\Bridge\Laravel;

use Illuminate\Support\ServiceProvider;
use Maatwebsite\Excel\Configuration;
use Maatwebsite\Excel\Drivers\PhpSpreadsheet\Driver;
use Maatwebsite\Excel\Excel;
use Maatwebsite\Excel\ExcelManager;

class ExcelServiceProvider extends ServiceProvider
{
    /**
     * @var string
     */
    const DS = DIRECTORY_SEPARATOR;

    /**
     * Register services.
     */
    public function register()
    {
        $this->mergeConfigFrom(
            $this->getConfigFile(),
            'excel'
        );

        $this->bindManager();

        $this->app->bind('excel', function () {
            return $this->app->make(ExcelManager::class)->get();
        });

        $this->app->alias('excel', Excel::class);
    }

    /**
     * Bind the Excel Manager.
     */
    protected function bindManager()
    {
        $this->app->singleton(ExcelManager::class, function () {
            $configuration = (new LaravelConfigBridge(
                $this->app->make('config'))
            )->toConfiguration();

            $manager = new ExcelManager($configuration);

            $manager->add(Driver::DRIVER_NAME, function () use ($configuration) {
                return $this->buildPhpSpreadsheet($configuration);
            });

            return $manager;
        });
    }

    /**
     * @param Configuration $configuration
     *
     * @return Excel
     */
    private function buildPhpSpreadsheet(Configuration $configuration)
    {
        return (new Driver($configuration))->buildLaravel($this->app['filesystem']);
    }

    /**
     * @return string
     */
    protected function getConfigFile(): string
    {
        return __DIR__.static::DS.'..'.static::DS.'..'.static::DS.'..'.static::DS.'config/excel.php';
    }
}
