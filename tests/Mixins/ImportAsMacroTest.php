<?php

declare(strict_types=1);

namespace Maatwebsite\Excel\Tests\Mixins;

use Maatwebsite\Excel\Tests\Data\Stubs\Database\User;
use Maatwebsite\Excel\Tests\TestCase;

final class ImportAsMacroTest extends TestCase
{
    /**
     * Setup the test environment.
     */
    protected function setUp(): void
    {
        parent::setUp();

        $this->loadLaravelMigrations(['--database' => 'testing']);
    }

    public function test_can_import_directly_into_a_model_with_mapping(): void
    {
        User::query()->truncate();

        User::importAs('import-users.xlsx', fn (array $row): array => [
            'name'     => $row[0],
            'email'    => $row[1],
            'password' => 'secret',
        ]);

        $this->assertCount(2, User::all());
        $this->assertSame([
            'patrick@maatwebsite.nl',
            'taylor@laravel.com',
        ], User::query()->pluck('email')->all());
    }
}
