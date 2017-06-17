<?php

namespace Maatwebsite\Excel\Bridge\Laravel;

use Illuminate\Support\ServiceProvider;
use Maatwebsite\Excel\Excel;
use Maatwebsite\Excel\ExcelManager;

class ExcelServiceProvider extends ServiceProvider
{
    /**
     * Register services.
     */
    public function register()
    {
        $this->bindManager();

        $this->app->bind('excel', function () {
            return $this->app->make(ExcelManager::class)->get();
        });

        $this->app->alias('excel', Excel::class);
    }

    /**
     * Bind the Excel Manager.
     */
    private function bindManager()
    {
        $this->app->singleton(ExcelManager::class, function () {
            $configuration = (new LaravelConfigBridge(
                $this->app->make('config'))
            )->toConfiguration();

            return new ExcelManager($configuration);
        });
    }
}
