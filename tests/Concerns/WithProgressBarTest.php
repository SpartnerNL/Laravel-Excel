<?php

declare(strict_types=1);

namespace Maatwebsite\Excel\Tests\Concerns;

use Illuminate\Console\OutputStyle;
use Maatwebsite\Excel\Concerns\Importable;
use Maatwebsite\Excel\Concerns\SkipsEmptyRows;
use Maatwebsite\Excel\Concerns\ToModel;
use Maatwebsite\Excel\Concerns\WithBatchInserts;
use Maatwebsite\Excel\Concerns\WithProgressBar;
use Maatwebsite\Excel\Tests\Data\Stubs\Database\Group;
use Maatwebsite\Excel\Tests\Data\Stubs\Database\User;
use Maatwebsite\Excel\Tests\TestCase;
use Mockery;

final class WithProgressBarTest extends TestCase
{
    /**
     * Setup the test environment.
     */
    protected function setUp(): void
    {
        parent::setUp();

        $this->loadLaravelMigrations(['--database' => 'testing']);
        $this->loadMigrationsFrom(__DIR__ . '/../Data/Stubs/Database/Migrations');
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

    public function test_reports_progress_of_skipped_rows_in_steps_of_the_batch_size(): void
    {
        $import = new class implements SkipsEmptyRows, ToModel, WithBatchInserts, WithProgressBar
        {
            use Importable;

            public function model(array $row): Group
            {
                return new Group([
                    'name' => $row[0],
                ]);
            }

            public function batchSize(): int
            {
                return 5;
            }

            /**
             * @param  array<array-key, mixed>  $row
             */
            public function isEmptyWhen(array $row): bool
            {
                return $row[0] === 'Empty';
            }
        };

        $advanced = [];
        $output   = Mockery::mock(OutputStyle::class);
        $output->shouldReceive('progressStart')
            ->once()
            ->with(10)
            ->andReturnSelf();
        $output->shouldReceive('progressAdvance')
            ->andReturnUsing(function (int $step) use (&$advanced): void {
                $advanced[] = $step;
            });
        $output->shouldReceive('progressFinish')
            ->once()
            ->andReturnSelf();

        $import->withOutput($output);

        // Row 4 of 10 is skipped: rows 1 to 6 fill the first batch, rows 7 to 10 are left over.
        $import->import('skip-empty-rows-with-is-empty-when.xlsx');

        $this->assertSame([5, 5], $advanced);
        $this->assertSame(9, Group::count());
    }
}
