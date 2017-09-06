<?php namespace Maatwebsite\Excel;

use PHPExcel_Settings;
use PHPExcel_Shared_Font;
use Maatwebsite\Excel\Readers\Html;
use Maatwebsite\Excel\Classes\Cache;
use Maatwebsite\Excel\Classes\PHPExcel;
use Illuminate\Support\ServiceProvider;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Response;
use Maatwebsite\Excel\Parsers\CssParser;
use Maatwebsite\Excel\Parsers\ViewParser;
use Maatwebsite\Excel\Classes\FormatIdentifier;
use Maatwebsite\Excel\Readers\LaravelExcelReader;
use Maatwebsite\Excel\Writers\LaravelExcelWriter;
use TijsVerkoyen\CssToInlineStyles\CssToInlineStyles;
use Laravel\Lumen\Application as LumenApplication;

/**
 *
 * LaravelExcel Excel ServiceProvider
 *
 * @category   Laravel Excel
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
        if ($this->app instanceof LumenApplication) {
            $this->app->configure('excel');
        } else {
            $this->publishes([
                __DIR__ . '/../../config/excel.php' => config_path('excel.php'),
            ]);
        }

        $this->mergeConfigFrom(
            __DIR__ . '/../../config/excel.php', 'excel'
        );

        //Set the autosizing settings
        $this->setAutoSizingSettings();
        
        //Enable an "export" method on Eloquent collections. ie: model::all()->export('file');
        Collection::macro('export', function($filename, $type = 'xlsx', $method = 'download') {
	        $model = $this;
	        Facades\Excel::create($filename, function($excel) use ($model, $filename) {
		        $excel->sheet($filename, function($sheet) use ($model) {
			        $sheet->fromModel($model);
		        });
	        })->$method($type);
        });
    }

    /**
     * Register the service provider.
     *
     * @return void
     */
    public function register()
    {
        $this->bindClasses();
        $this->bindCssParser();
        $this->bindReaders();
        $this->bindParsers();
        $this->bindPHPExcelClass();
        $this->bindWriters();
        $this->bindExcel();
    }

    /**
     * Bind PHPExcel classes
     * @return void
     */
    protected function bindPHPExcelClass()
    {
        // Set object
        $me = $this;

        // Bind the PHPExcel class
        $this->app->singleton('phpexcel', function () use ($me)
        {
            // Set locale
            $me->setLocale();

            // Set the caching settings
            $me->setCacheSettings();

            // Init phpExcel
            $excel = new PHPExcel();
            $excel->setDefaultProperties();
            return $excel;
        });
    }

    /**
     * Bind the css parser
     */
    protected function bindCssParser()
    {
        // Bind css parser
        $this->app->singleton('excel.parsers.css', function ()
        {
            return new CssParser(
                new CssToInlineStyles()
            );
        });
    }

    /**
     * Bind writers
     * @return void
     */
    protected function bindReaders()
    {
        // Bind the laravel excel reader
        $this->app->singleton('excel.reader', function ($app)
        {
            return new LaravelExcelReader(
                $app['files'],
                $app['excel.identifier'],
                $app['Illuminate\Contracts\Bus\Dispatcher']
            );
        });

        // Bind the html reader class
        $this->app->singleton('excel.readers.html', function ($app)
        {
            return new Html(
                $app['excel.parsers.css']
            );
        });
    }

    /**
     * Bind writers
     * @return void
     */
    protected function bindParsers()
    {
        // Bind the view parser
        $this->app->singleton('excel.parsers.view', function ($app)
        {
            return new ViewParser(
                $app['excel.readers.html']
            );
        });
    }

    /**
     * Bind writers
     * @return void
     */
    protected function bindWriters()
    {
        // Bind the excel writer
        $this->app->singleton('excel.writer', function ($app)
        {
            return new LaravelExcelWriter(
                $app->make(Response::class),
                $app['files'],
                $app['excel.identifier']
            );
        });
    }

    /**
     * Bind Excel class
     * @return void
     */
    protected function bindExcel()
    {
        // Bind the Excel class and inject its dependencies
        $this->app->singleton('excel', function ($app)
        {
            $excel = new Excel(
                $app['phpexcel'],
                $app['excel.reader'],
                $app['excel.writer'],
                $app['excel.parsers.view']
            );

            $excel->registerFilters($app['config']->get('excel.filters', array()));

            return $excel;
        });
        
        $this->app->alias('phpexcel', PHPExcel::class);
    }

    /**
     * Bind other classes
     * @return void
     */
    protected function bindClasses()
    {
        // Bind the format identifier
        $this->app->singleton('excel.identifier', function ($app)
        {
            return new FormatIdentifier($app['files']);
        });
    }

    /**
     * Set cache settings
     * @return Cache
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
        $locale = config('app.locale', 'en_us');
        PHPExcel_Settings::setLocale($locale);
    }

    /**
     * Set the autosizing settings
     */
    public function setAutoSizingSettings()
    {
        $method = config('excel.export.autosize-method', PHPExcel_Shared_Font::AUTOSIZE_METHOD_APPROX);
        PHPExcel_Shared_Font::setAutoSizeMethod($method);
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
            'phpexcel',
            'excel.reader',
            'excel.readers.html',
            'excel.parsers.view',
            'excel.writer'
        ];
    }
}
