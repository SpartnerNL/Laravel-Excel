<?php

namespace Maatwebsite\Excel\Tests\Concerns;

use Carbon\Carbon;
use Maatwebsite\Excel\Columns\Boolean;
use Maatwebsite\Excel\Columns\Date;
use Maatwebsite\Excel\Columns\Decimal;
use Maatwebsite\Excel\Columns\EmptyCell;
use Maatwebsite\Excel\Columns\Formula;
use Maatwebsite\Excel\Columns\Hyperlink;
use Maatwebsite\Excel\Columns\Number;
use Maatwebsite\Excel\Columns\Price;
use Maatwebsite\Excel\Columns\RichText;
use Maatwebsite\Excel\Columns\Text;
use Maatwebsite\Excel\Concerns\Importable;
use Maatwebsite\Excel\Concerns\ToArray;
use Maatwebsite\Excel\Concerns\ToModel;
use Maatwebsite\Excel\Concerns\WithColumns;
use Maatwebsite\Excel\Concerns\WithHeadingRow;
use Maatwebsite\Excel\Tests\Data\Stubs\Database\User;
use Maatwebsite\Excel\Tests\TestCase;
use PHPUnit\Framework\Assert;

class WithColumnsImportTest extends TestCase
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

    /**
     * @test
     */
    public function can_import_from_columns_to_model()
    {
        $import = new class implements ToModel, WithColumns
        {
            use Importable;

            public function model(array $row): string
            {
                return User::class;
            }

            public function columns(): array
            {
                return [
                    Text::make('name', function (string $name) {
                        return 'Mapped ' . $name;
                    }),
                    Text::make('email'),
                ];
            }
        };

        $import->import('import-users.xlsx');

        $this->assertDatabaseHas('users', [
            'name'  => 'Mapped Patrick Brouwers',
            'email' => 'patrick@maatwebsite.nl',
        ]);

        $this->assertDatabaseHas('users', [
            'name'  => 'Mapped Taylor Otwell',
            'email' => 'taylor@laravel.com',
        ]);
    }

    /**
     * @test
     */
    public function can_import_from_columns_to_array()
    {
        $import = new class implements ToArray, WithColumns
        {
            use Importable;

            public function array(array $array)
            {
                Assert::assertEquals([
                    [
                        'name'           => 'Patrick Brouwers',
                        'email'          => 'patrick@maatwebsite.nl',
                        'date_of_birth'  => Carbon::make('1993-02-07'),
                        'number'         => 1000,
                        'percentage'     => 1.0,
                        'price'          => 100,
                        'formula'        => '=1+1',
                        'html'           => 'test <span style="font-weight:bold; color:#000000; font-family:\'Calibri\'; font-size:11pt">bold</span><span style="color:#000000; font-family:\'Calibri\'; font-size:11pt"> test</span>',
                        'decimal'        => 10.5,
                        'boolean'        => true,
                        'hyperlink_name' => 'Maatwebsite',
                        'hyperlink_url'  => 'https://maatwebsite.com/',
                    ],
                    [
                        'name'           => 'Taylor Otwell',
                        'email'          => 'taylor@laravel.com',
                        'date_of_birth'  => Carbon::make('1986-05-28'),
                        'number'         => 2000,
                        'percentage'     => 2.0,
                        'price'          => 200,
                        'formula'        => '=2+3',
                        'html'           => 'test <span style="font-weight:bold; color:#000000; font-family:\'Calibri\'; font-size:11pt">bold</span><span style="color:#000000; font-family:\'Calibri\'; font-size:11pt"> test</span>',
                        'decimal'        => 20.5,
                        'boolean'        => false,
                        'hyperlink_name' => 'Laravel',
                        'hyperlink_url'  => 'https://laravel.com/',
                    ],
                ], $array);
            }

            public function columns(): array
            {
                return [
                    'B' => Text::make('name'),
                    'C' => Text::make('email'),
                    'D' => Date::make('date_of_birth'),
                    'E' => Number::make('number'),
                    'F' => EmptyCell::make(),
                    'G' => Decimal::make('percentage'),
                    'H' => Price::make('price'),
                    'I' => Formula::make('formula'),
                    'J' => RichText::make('html'),
                    'K' => Decimal::make('decimal'),
                    'L' => Boolean::make('boolean'),
                    'M' => [
                        Hyperlink::make('hyperlink_name'),
                        Hyperlink::make('hyperlink_url')->url(),
                    ],
                ];
            }
        };

        $import->import('import-users-with-columns.xlsx');
    }

    /**
     * @test
     */
    public function can_import_from_columns_with_heading_row()
    {
        $import = new class implements ToArray, WithHeadingRow, WithColumns
        {
            use Importable;

            public function array(array $array)
            {
                Assert::assertEquals([
                    [
                        'name'         => 'Patrick Brouwers',
                        'email'        => 'patrick@maatwebsite.nl',
                        'non_existing' => null,
                    ],
                    [
                        'name'         => 'Taylor Otwell',
                        'email'        => 'taylor@laravel.com',
                        'non_existing' => null,
                    ],
                ], $array);
            }

            public function columns(): array
            {
                return [
                    'name'         => Text::make('name'),
                    'email'        => Text::make('email'),
                    'non_existing' => Text::make('non_existing'),
                ];
            }
        };

        $import->import('import-users-with-columns-heading.xlsx');
    }
}
