<?php

namespace Maatwebsite\Excel;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Collection;
use Illuminate\Support\ServiceProvider;
use Laravel\Lumen\Application as LumenApplication;
use Maatwebsite\Excel\Cache\CacheManager;
use Maatwebsite\Excel\Config\Configuration;
use Maatwebsite\Excel\Config\SettingsProvider;
use Maatwebsite\Excel\Console\ExportMakeCommand;
use Maatwebsite\Excel\Console\ImportMakeCommand;
use Maatwebsite\Excel\Files\Filesystem;
use Maatwebsite\Excel\Files\TemporaryFileFactory;
use Maatwebsite\Excel\Mixins\DownloadCollectionMixin;
use Maatwebsite\Excel\Mixins\DownloadQueryMacro;
use Maatwebsite\Excel\Mixins\ImportAsMacro;
use Maatwebsite\Excel\Mixins\ImportMacro;
use Maatwebsite\Excel\Mixins\StoreCollectionMixin;
use Maatwebsite\Excel\Mixins\StoreQueryMacro;
use Maatwebsite\Excel\Transactions\TransactionHandler;
use Maatwebsite\Excel\Transactions\TransactionManager;

class ExcelServiceProvider extends ServiceProvider
{
    /**
     * {@inheritdoc}
     */
    public function register()
    {
        $this->mergeConfigFrom(
            $this->getConfigFile(),
            'excel'
        );

        $this->bindManagers();
        $this->bindFactories();
        $this->bindServices();
        $this->bindAliases();
        $this->bindMixins();

        $this->commands([
            ExportMakeCommand::class,
            ImportMakeCommand::class,
        ]);
    }

    /**
     * {@inheritdoc}
     */
    public function boot()
    {
        if (!$this->app instanceof LumenApplication && $this->app->runningInConsole()) {
            $this->publishes([
                $this->getConfigFile() => config_path('excel.php'),
            ], 'config');
        }

        $this->app->booted(function () {
            $this->app->make(SettingsProvider::class)->provide();
        });
    }

    /**
     * Bind managers.
     */
    private function bindManagers()
    {
        $this->app->bind(CacheManager::class, function () {
            return new CacheManager($this->app);
        });

        $this->app->bind(TransactionManager::class, function () {
            return new TransactionManager($this->app);
        });
    }

    /**
     * Bind factories.
     */
    private function bindFactories()
    {
        $this->app->bind(TemporaryFileFactory::class, function () {
            return new TemporaryFileFactory(
                Configuration::getLocalTemporaryPath(),
                Configuration::getRemoteTemporaryDisk()
            );
        });
    }

    /**
     * Bind services.
     */
    private function bindServices()
    {
        $this->app->bind(Filesystem::class, function () {
            return new Filesystem($this->app->make('filesystem'));
        });

        $this->app->bind('excel', function () {
            return new Excel(
                $this->app->make(Writer::class),
                $this->app->make(Reader::class),
                $this->app->make(Filesystem::class)
            );
        });
    }

    /**
     * Bind aliases.
     */
    private function bindAliases()
    {
        $this->app->bind(TransactionHandler::class, function () {
            return $this->app->make(TransactionManager::class)->driver();
        });

        $this->app->alias('excel', Excel::class);
        $this->app->alias('excel', Exporter::class);
        $this->app->alias('excel', Importer::class);
    }

    /**
     * Bind mixins.
     */
    private function bindMixins()
    {
        Collection::mixin(new DownloadCollectionMixin);
        Collection::mixin(new StoreCollectionMixin);
        Builder::macro('downloadExcel', (new DownloadQueryMacro)());
        Builder::macro('storeExcel', (new StoreQueryMacro())());
        Builder::macro('import', (new ImportMacro())());
        Builder::macro('importAs', (new ImportAsMacro())());
    }

    /**
     * @return string
     */
    private function getConfigFile(): string
    {
        return __DIR__ . DIRECTORY_SEPARATOR . '..' . DIRECTORY_SEPARATOR . 'config' . DIRECTORY_SEPARATOR . 'excel.php';
    }
}
