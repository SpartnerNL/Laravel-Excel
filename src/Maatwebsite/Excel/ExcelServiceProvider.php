<?php namespace Maatwebsite\Excel;

use Illuminate\Support\ServiceProvider;

class ExcelServiceProvider extends ServiceProvider {

	/**
	 * Indicates if loading of the provider is deferred.
	 *
	 * @var bool
	 */
	protected $defer = false;

	/**
	 * Bootstrap the application events.
	 *
	 * @return void
	 */

	public function boot()
	{
		$this->package('maatwebsite/excel');
	}

	/**
	 * Register the service provider.
	 *
	 * @return void
	 */
	public function register()
	{
		$this->app['excel'] = $this->app->share(function($app)
        {
            return new Maatwebsite\Excel\Excel;
        });
	}

	/**
	 * Get the services provided by the provider.
	 *
	 * @return array
	 */
	public function provides()
	{
		return array('excel');
	}

}