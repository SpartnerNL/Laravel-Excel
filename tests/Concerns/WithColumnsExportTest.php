<?php

namespace Maatwebsite\Excel\Tests\Concerns;

use Maatwebsite\Excel\Columns\Number;
use Maatwebsite\Excel\Columns\Text;
use Maatwebsite\Excel\Concerns\Exportable;
use Maatwebsite\Excel\Concerns\FromQuery;
use Maatwebsite\Excel\Concerns\WithColumns;
use Maatwebsite\Excel\Tests\Data\Stubs\Database\Group;
use Maatwebsite\Excel\Tests\Data\Stubs\Database\User;
use Maatwebsite\Excel\Tests\TestCase;

class WithColumnsExportTest extends TestCase
{
    /**
     * Setup the test environment.
     */
    protected function setUp(): void
    {
        parent::setUp();

        $this->loadLaravelMigrations(['--database' => 'testing']);
        $this->withFactories(__DIR__ . '/../Data/Stubs/Database/Factories');
        $this->loadMigrationsFrom(dirname(__DIR__) . '/Data/Stubs/Database/Migrations');

        $group = factory(Group::class)->create([
            'name' => 'Group 1',
        ]);

        factory(User::class)->times(100)->create()->each(function (User $user) use ($group) {
            $user->groups()->save($group);
        });

        $group_two = factory(Group::class)->create([
            'name' => 'Group 2',
        ]);

        factory(User::class)->times(5)->create()->each(function (User $user) use ($group_two) {
            $user->groups()->save($group_two);
        });
    }

    /**
     * @test
     */
    public function can_export_from_query_with_columns()
    {
        $export = new class implements FromQuery, WithColumns
        {
            use Exportable;

            public function query()
            {
                return User::query();
            }

            public function columns(): array
            {
                return [
                    Number::make('ID', 'id'),
                    Text::make('Name', 'name'),
                    Text::make('Email', 'email'),
                    Text::make('Custom', function (User $user) {
                        return strtoupper($user->name);
                    }),
                ];
            }
        };

        $response = $export->store('with-columns-export.xlsx');

        $this->assertTrue($response);

        $contents = $this->readAsArray(__DIR__ . '/../Data/Disks/Local/with-columns-export.xlsx', 'Xlsx');

        $expected = $export->query()->get()->map(function (User $user) {
            return array_merge(array_values($user->only('id', 'name', 'email')), [strtoupper($user->name)]);
        })->prepend(['ID', 'Name', 'Email', 'Custom'])->values()->toArray();

        $this->assertEquals($expected, $contents);
    }
}
