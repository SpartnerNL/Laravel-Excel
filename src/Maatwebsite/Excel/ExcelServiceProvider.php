<?php namespace Maatwebsite\Excel;

use \Config;
use \PHPExcel_Settings;
use Maatwebsite\Excel\Classes\Cache;
use Maatwebsite\Excel\Classes\PHPExcel;
use Illuminate\Support\ServiceProvider;
use Maatwebsite\Excel\Readers\Html;
use Maatwebsite\Excel\Readers\LaravelExcelReader;
use Maatwebsite\Excel\Writers\LaravelExcelWriter;
use Maatwebsite\Excel\Classes\LaravelExcelWorksheet;
use Maatwebsite\Excel\Parsers\ViewParser;

/**
 *
 * LaravelExcel Excel ServiceProvider
 *
 * @category   Laravel Excel
 * @version    1.0.0
 * @package    maatwebsite/excel
 * @copyright  Copyright (c) 2013 - 2014 Maatwebsite (http://www.maatwebsite.nl)
 * @author     Maatwebsite <info@maatwebsite.nl>
 * @license    http://www.gnu.org/licenses/old-licenses/lgpl-2.1.txt    LGPL
 */
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
		$this->bindReaders();
		$this->bindParsers();
		$this->bindPHPExcelClass();
		$this->bindWriters();
		$this->bindExcel();
	}

	/**
	 * Bind PHPExcel classes
	 * @return [type] [description]
	 */
	protected function bindPHPExcelClass()
	{

		// Set object
		$me = $this;

		// Bind the PHPExcel class
		$this->app['phpexcel'] = $this->app->share(function($app) use($me) {

			// Set locale
			$me->setLocale();

			// Set the caching settings
			$me->setCacheSettings();

			// Init phpExcel
			return new PHPExcel();
		});
	}

	/**
	 * Bind writers
	 * @return [type] [description]
	 */
	protected function bindReaders()
	{
		// Bind the laravel excel reader
		$this->app['excel.reader'] = $this->app->share(function($app)
		{
			return new LaravelExcelReader($app['files']);
		});

		// Bind the html reader class
		$this->app['excel.readers.html'] = $this->app->share(function($app)
		{
			return new Html();
		});
	}

	/**
	 * Bind writers
	 * @return [type] [description]
	 */
	protected function bindParsers()
	{
		// Bind the view parser
		$this->app['excel.parsers.view'] = $this->app->share(function($app)
		{
			return new ViewParser($app['excel.readers.html']);
		});
	}

	/**
	 * Bind writers
	 * @return [type] [description]
	 */
	protected function bindWriters()
	{
		// Bind the excel writer
		$this->app['excel.writer'] = $this->app->share(function($app)
		{
			return new LaravelExcelWriter($app->make('Response'), $app['files']);
		});
	}

	/**
	 * Bind Excel class
	 * @return [type] [description]
	 */
	protected function bindExcel()
	{
		// Bind the Excel class and inject its dependencies
		$this->app['excel'] = $this->app->share(function($app)
        {
            return new Excel($app['phpexcel'], $app['excel.reader'], $app['excel.writer'], $app['excel.parsers.view']);
        });
	}

	/**
	 * Set the cache settings
	 */
	public function setCacheSettings()
	{
		return new Cache();
	}

	/**
	 * Set locale
	 */
	public function setLocale()
	{
		$locale = Config::get('app.locale', 'en_us');
		PHPExcel_Settings::setLocale($locale);
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