<?php

namespace Maatwebsite\Excel;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Foundation\Application;
use Illuminate\Support\Collection;
use Illuminate\Support\ServiceProvider;
use Laravel\Lumen\Application as LumenApplication;
use Maatwebsite\Excel\Cache\CacheManager;
use Maatwebsite\Excel\Console\ExportMakeCommand;
use Maatwebsite\Excel\Console\ImportMakeCommand;
use Maatwebsite\Excel\Files\Filesystem;
use Maatwebsite\Excel\Files\TemporaryFileFactory;
use Maatwebsite\Excel\Handlers\FromArrayHandler;
use Maatwebsite\Excel\Handlers\FromCollectionHandler;
use Maatwebsite\Excel\Handlers\FromGeneratorHandler;
use Maatwebsite\Excel\Handlers\FromIteratorHandler;
use Maatwebsite\Excel\Handlers\FromQueryHandler;
use Maatwebsite\Excel\Handlers\FromScoutHandler;
use Maatwebsite\Excel\Handlers\FromViewHandler;
use Maatwebsite\Excel\Mixins\DownloadCollectionMixin;
use Maatwebsite\Excel\Mixins\DownloadQueryMacro;
use Maatwebsite\Excel\Mixins\ImportAsMacro;
use Maatwebsite\Excel\Mixins\ImportMacro;
use Maatwebsite\Excel\Mixins\StoreCollectionMixin;
use Maatwebsite\Excel\Mixins\StoreQueryMacro;
use Maatwebsite\Excel\Transactions\TransactionHandler;
use Maatwebsite\Excel\Transactions\TransactionManager;
use Override;

class ExcelServiceProvider extends ServiceProvider
{
    public function boot(): void
    {
        if ($this->app->runningInConsole()) {
            $this->publishes([
                __DIR__ . '/Console/stubs/export.model.stub'       => base_path('stubs/export.model.stub'),
                __DIR__ . '/Console/stubs/export.plain.stub'       => base_path('stubs/export.plain.stub'),
                __DIR__ . '/Console/stubs/export.query.stub'       => base_path('stubs/export.query.stub'),
                __DIR__ . '/Console/stubs/export.query-model.stub' => base_path('stubs/export.query-model.stub'),
                __DIR__ . '/Console/stubs/import.collection.stub'  => base_path('stubs/import.collection.stub'),
                __DIR__ . '/Console/stubs/import.model.stub'       => base_path('stubs/import.model.stub'),
            ], 'stubs');

            if (class_exists(LumenApplication::class) && $this->app instanceof LumenApplication) {
                $this->app->configure('excel');
            } else {
                $this->publishes([
                    $this->getConfigFile() => config_path('excel.php'),
                ], 'config');
            }
        }

        if ($this->app instanceof Application) {
            // Laravel
            $this->app->booted(function ($app): void {
                $app->make(SettingsProvider::class)->provide();
            });
        } else {
            // Lumen
            $this->app->make(SettingsProvider::class)->provide();
        }
    }

    /**
     * {@inheritdoc}
     */
    #[Override]
    public function register(): void
    {
        $this->mergeConfigFrom(
            $this->getConfigFile(),
            'excel'
        );

        $this->app->singleton(function (): HandlerRegistry {
            $registry = new HandlerRegistry;

            $registry->register(
                FromGeneratorHandler::class,
                FromIteratorHandler::class,
                FromArrayHandler::class,
                FromCollectionHandler::class,
                FromScoutHandler::class,
                FromQueryHandler::class,
                FromViewHandler::class,
                ...config('excel.exports.source_handlers', []),
            );

            return $registry;
        });

        $this->app->bind(CacheManager::class, fn ($app): CacheManager => new CacheManager($app));

        $this->app->singleton(TransactionManager::class, fn ($app): TransactionManager => new TransactionManager($app));

        $this->app->bind(TransactionHandler::class, fn ($app) => $app->make(TransactionManager::class)->driver());

        $this->app->bind(TemporaryFileFactory::class, fn (): TemporaryFileFactory => new TemporaryFileFactory(
            config('excel.temporary_files.local_path', config('excel.exports.temp_path', storage_path('framework/laravel-excel'))),
            config('excel.temporary_files.remote_disk')
        ));

        $this->app->bind(Filesystem::class, fn ($app): Filesystem => new Filesystem($app->make('filesystem')));

        $this->app->bind('excel', fn ($app): Excel => new Excel(
            $app->make(Writer::class),
            $app->make(QueuedWriter::class),
            $app->make(Reader::class),
            $app->make(Filesystem::class),
            $app->make(HandlerRegistry::class),
        ));

        $this->app->alias('excel', Excel::class);
        $this->app->alias('excel', Exporter::class);
        $this->app->alias('excel', Importer::class);

        Collection::mixin(new DownloadCollectionMixin);
        Collection::mixin(new StoreCollectionMixin);
        Builder::macro('downloadExcel', (new DownloadQueryMacro)());
        Builder::macro('storeExcel', (new StoreQueryMacro)());
        Builder::macro('import', (new ImportMacro)());
        Builder::macro('importAs', (new ImportAsMacro)());

        $this->commands([
            ExportMakeCommand::class,
            ImportMakeCommand::class,
        ]);
    }

    protected function getConfigFile(): string
    {
        return __DIR__ . DIRECTORY_SEPARATOR . '..' . DIRECTORY_SEPARATOR . 'config' . DIRECTORY_SEPARATOR . 'excel.php';
    }
}
