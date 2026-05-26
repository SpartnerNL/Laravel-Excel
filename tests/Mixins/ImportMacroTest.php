<?php

declare(strict_types=1);

namespace Maatwebsite\Excel\Tests\Mixins;

use Maatwebsite\Excel\Tests\Data\Stubs\Database\User;
use Maatwebsite\Excel\Tests\TestCase;

final class ImportMacroTest extends TestCase
{
    /**
     * Setup the test environment.
     */
    protected function setUp(): void
    {
        parent::setUp();

        $this->loadLaravelMigrations(['--database' => 'testing']);
    }

    public function test_can_import_directly_into_a_model(): void
    {
        User::query()->truncate();
        User::creating(function ($user): void {
            $user->password = 'secret';
        });

        /** @phpstan-ignore staticMethod.notFound */
        User::import('import-users-with-headings.xlsx');

        $this->assertCount(2, User::all());
        $this->assertEquals([
            'patrick@maatwebsite.nl',
            'taylor@laravel.com',
        ], User::query()->pluck('email')->all());
    }
}
