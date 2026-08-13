<?php

declare(strict_types=1);

namespace Maatwebsite\Excel\Tests\Concerns;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Facades\Storage;
use Maatwebsite\Excel\Columns\Column;
use Maatwebsite\Excel\Columns\Image;
use Maatwebsite\Excel\Columns\Number;
use Maatwebsite\Excel\Columns\Text;
use Maatwebsite\Excel\Concerns\Exportable;
use Maatwebsite\Excel\Concerns\FromArray;
use Maatwebsite\Excel\Concerns\FromQuery;
use Maatwebsite\Excel\Concerns\WithColumns;
use Maatwebsite\Excel\Tests\Data\Stubs\Database\User;
use Maatwebsite\Excel\Tests\TestCase;

final class WithColumnsExportTest extends TestCase
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

    public function test_can_export_from_query_with_columns(): void
    {
        User::factory()->count(100)->create();

        $export = new class implements FromQuery, WithColumns
        {
            use Exportable;

            /**
             * @return Builder<User>
             */
            public function query(): Builder
            {
                return User::query();
            }

            /**
             * @return Column[]
             */
            public function columns(): array
            {
                return [
                    Number::make('ID', 'id'),
                    Text::make('Name', 'name'),
                    Text::make('Email', 'email'),
                    Text::make('Custom', fn (User $user): string => strtoupper($user->name)),
                ];
            }
        };

        $this->assertTrue($export->store('with-columns-export.xlsx'));

        $contents = $this->readAsArray(__DIR__ . '/../Data/Disks/Local/with-columns-export.xlsx', 'Xlsx');

        $expected = $export->query()->get()
            ->map(fn (User $user): array => array_merge(
                array_values($user->only('id', 'name', 'email')),
                [strtoupper($user->name)]
            ))
            ->values()
            ->toArray();

        array_unshift($expected, ['ID', 'Name', 'Email', 'Custom']);

        $this->assertEquals($expected, $contents);
    }

    public function test_can_export_from_array_with_columns(): void
    {
        Storage::disk('local')->delete(['avatar-1.jpg', 'avatar-2.jpg']);
        Storage::disk('local')->copy('icon.jpg', 'avatar-1.jpg');
        Storage::disk('local')->copy('icon.jpg', 'avatar-2.jpg');

        $export = new class implements FromArray, WithColumns
        {
            use Exportable;

            public function array(): array
            {
                return [
                    [
                        'id'     => 1,
                        'avatar' => 'avatar-1.jpg',
                    ],
                    [
                        'id'     => 2,
                        'avatar' => 'avatar-2.jpg',
                    ],
                ];
            }

            /**
             * @return Column[]
             */
            public function columns(): array
            {
                return [
                    Number::make('ID', 'id'),
                    Image::make('Avatar', fn (array $row): string => Storage::disk('local')->path($row['avatar']))->height(25),
                    Image::make('Avatar2', 'avatar')->disk('local')->height(25),
                ];
            }
        };

        $this->assertTrue($export->store('with-columns-export.xlsx'));

        $spreadsheet = $this->read(__DIR__ . '/../Data/Disks/Local/with-columns-export.xlsx', 'Xlsx');

        $this->assertCount(4, $spreadsheet->getActiveSheet()->getDrawingCollection());

        $this->assertEquals([
            ['ID', 'Avatar', 'Avatar2'],
            [1, null, null],
            [2, null, null],
        ], $spreadsheet->getActiveSheet()->toArray());
    }
}
