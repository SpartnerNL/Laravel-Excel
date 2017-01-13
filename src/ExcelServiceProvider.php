<?php

namespace Maatwebsite\Excel;

use Illuminate\Support\ServiceProvider;

/**
 * LaravelExcel Excel ServiceProvider.
 *
 * @category   Laravel Excel
 * @package    maatwebsite/excel
 * @copyright  Copyright (c) 2013 - 2014 Maatwebsite (http://www.maatwebsite.nl)
 * @author     Maatwebsite <info@maatwebsite.nl>
 * @license    http://www.gnu.org/licenses/old-licenses/lgpl-2.1.txt    LGPL
 */
class ExcelServiceProvider extends ServiceProvider
{
    /**
     * Indicates if loading of the provider is deferred.
     *
     * @var bool
     */
    protected $defer = true;

    /**
     * Bootstrap the application events.
     */
    public function boot()
    {
        $this->publishes([
            __DIR__ . '/../config/excel.php' => config_path('excel.php'),
        ]);

        $this->mergeConfigFrom(
            __DIR__ . '/../config/excel.php', 'excel'
        );
    }

    /**
     * Register the service provider.
     */
    public function register()
    {
        $this->app->singleton('excel.manager', function ($app) {
            return new ExcelManager($app);
        });

        $this->app->alias('excel', Excel::class);

        $this->app->bind('excel', function ($app) {
            return $app['excel.manager']->driver(
                $app->config->get('excel.driver')
            );
        });
    }

    /**
     * Get the services provided by the provider.
     *
     * @return array
     */
    public function provides()
    {
        return [
            'excel',
            'excel.manager',
        ];
    }
}
