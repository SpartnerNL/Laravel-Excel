<?php

namespace Maatwebsite\Excel;

use Illuminate\Support\Collection;
use Illuminate\Support\ServiceProvider;
use Maatwebsite\Excel\Mixins\StoreCollection;
use Maatwebsite\Excel\Mixins\DownloadCollection;
use Illuminate\Contracts\Routing\ResponseFactory;

class ExcelServiceProvider extends ServiceProvider
{
    /**
     * {@inheritdoc}
     */
    public function boot()
    {
        if ($this->app->runningInConsole()) {
            $this->publishes([
                $this->getConfigFile() => config_path('excel.php'),
            ], 'config');
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

        $this->app->bind('excel', function () {
            return new Excel(
                $this->app->make(Writer::class),
                $this->app->make(QueuedWriter::class),
                $this->app->make(ResponseFactory::class),
                $this->app->make('filesystem')
            );
        });

        $this->app->alias('excel', Excel::class);
        $this->app->alias('excel', Exporter::class);

        Collection::mixin(new DownloadCollection);
        Collection::mixin(new StoreCollection);
    }

    /**
     * @return string
     */
    protected function getConfigFile(): string
    {
        return __DIR__ . DIRECTORY_SEPARATOR . '..' . DIRECTORY_SEPARATOR . 'config' . DIRECTORY_SEPARATOR . 'excel.php';
    }
}
