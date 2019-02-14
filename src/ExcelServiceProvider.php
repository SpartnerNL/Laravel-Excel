<?php

namespace Maatwebsite\Excel;

use Illuminate\Support\Collection;
use Illuminate\Support\ServiceProvider;
use Maatwebsite\Excel\Helpers\FilePathHelper;
use Maatwebsite\Excel\Mixins\StoreCollection;
use Maatwebsite\Excel\Console\ExportMakeCommand;
use Maatwebsite\Excel\Console\ImportMakeCommand;
use Maatwebsite\Excel\Mixins\DownloadCollection;
use Laravel\Lumen\Application as LumenApplication;

class ExcelServiceProvider extends ServiceProvider
{
    /**
     * {@inheritdoc}
     */
    public function boot()
    {
        if ($this->app->runningInConsole()) {
            if ($this->app instanceof LumenApplication) {
                $this->app->configure('excel');
            } else {
                $this->publishes([
                    $this->getConfigFile() => config_path('excel.php'),
                ], 'config');
            }
        }
    }

    /**
     * {@inheritdoc}
     */
    public function register()
    {
        $this->mergeConfigFrom(
            $this->getConfigFile(),
            'excel'
        );

        $this->app->bind(Reader::class, function () {
            $config = $this->app->make('config');

            return new Reader(
                new FilePathHelper(
                    $this->app->make('filesystem'),
                    $config->get('excel.exports.temp_path', sys_get_temp_dir())
                ),
                $config->get('excel.imports.csv', $config->get('excel.exports.csv', []))
            );
        });

        $this->app->bind('excel', function () {
            return new Excel(
                $this->app->make(Writer::class),
                $this->app->make(QueuedWriter::class),
                $this->app->make(Reader::class),
                $this->app->make('filesystem')
            );
        });

        $this->app->alias('excel', Excel::class);
        $this->app->alias('excel', Exporter::class);
        $this->app->alias('excel', Importer::class);

        Collection::mixin(new DownloadCollection);
        Collection::mixin(new StoreCollection);

        $this->commands([
            ExportMakeCommand::class,
            ImportMakeCommand::class,
        ]);
    }

    /**
     * @return string
     */
    protected function getConfigFile(): string
    {
        return __DIR__ . DIRECTORY_SEPARATOR . '..' . DIRECTORY_SEPARATOR . 'config' . DIRECTORY_SEPARATOR . 'excel.php';
    }
}
