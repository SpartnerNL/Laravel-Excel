<?php

namespace Maatwebsite\Excel\Tests\Concerns;

use Illuminate\Console\OutputStyle;
use Maatwebsite\Excel\Concerns\Importable;
use Maatwebsite\Excel\Concerns\ToModel;
use Maatwebsite\Excel\Concerns\WithProgressBar;
use Maatwebsite\Excel\Tests\Data\Stubs\Database\User;
use Maatwebsite\Excel\Tests\TestCase;
use Mockery;

class WithProgressBarTest extends TestCase
{
    /**
     * Setup the test environment.
     */
    protected function setUp(): void
    {
        parent::setUp();

        $this->loadLaravelMigrations(['--database' => 'testing']);
        $this->loadMigrationsFrom(dirname(__DIR__) . '/Data/Stubs/Database/Migrations');
    }

    public function test_reports_progress(): void
    {
        $import = new class implements ToModel, WithProgressBar
        {
            use Importable;

            public function model(array $row): User
            {
                return new User([
                    'name'     => $row[0],
                    'email'    => $row[1],
                    'password' => 'secret',
                ]);
            }
        };

        $output = Mockery::mock(OutputStyle::class);
        $output->shouldReceive('progressStart')
            ->once()
            ->with(2)
            ->andReturnSelf();
        $output->shouldReceive('progressAdvance')
            ->twice()
            ->andReturnSelf();
        $output->shouldReceive('progressFinish')
            ->once()
            ->andReturnSelf();

        $import->withOutput($output);
        $import->import('import-users.xlsx');
        $this->assertEquals(2, User::count());
    }
}
