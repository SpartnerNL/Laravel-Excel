<?php

namespace Maatwebsite\Excel\Tests;

use Illuminate\Support\ServiceProvider;

class TestAppConfigProvider extends ServiceProvider
{
    public function boot(): void
    {
        $this->app['config']->set('filesystems.disks.local.root', __DIR__ . '/Data/Disks/Local');
        $this->app['config']->set('filesystems.disks.test', [
            'driver' => 'local',
            'root'   => __DIR__ . '/Data/Disks/Test',
        ]);

        $this->app['config']->set('database.default', 'testing');
        $this->app['config']->set('database.connections.testing', [
            'driver'   => 'sqlite',
            'database' => ':memory:',
        ]);

        $this->app['config']->set('view.paths', [
            __DIR__ . '/Data/Stubs/Views',
        ]);
    }
}
