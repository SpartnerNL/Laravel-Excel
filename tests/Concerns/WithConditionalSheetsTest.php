<?php

namespace Maatwebsite\Excel\Tests\Concerns;

use Maatwebsite\Excel\Concerns\SkipsUnknownSheets;
use Maatwebsite\Excel\Concerns\WithConditionalSheets;
use PHPUnit\Framework\Assert;
use Illuminate\Support\Collection;
use Maatwebsite\Excel\Tests\TestCase;
use Maatwebsite\Excel\Concerns\ToArray;
use Maatwebsite\Excel\Concerns\Exportable;
use Maatwebsite\Excel\Concerns\Importable;
use Maatwebsite\Excel\Concerns\WithMultipleSheets;
use Maatwebsite\Excel\Tests\Data\Stubs\Database\User;
use Maatwebsite\Excel\Tests\Data\Stubs\SheetWith100Rows;
use Maatwebsite\Excel\Tests\Data\Stubs\SheetForUsersFromView;

class WithConditionalSheetsTest extends TestCase
{
    /**
     * Setup the test environment.
     */
    protected function setUp()
    {
        parent::setUp();

        $this->withFactories(__DIR__ . '/../Data/Stubs/Database/Factories');
    }

    /**
     * @test
     */
    public function can_select_which_sheets_will_be_imported()
    {
        $import = new class implements WithMultipleSheets
        {
            use Importable, WithConditionalSheets;

            public $sheets = [];

            public function __construct()
            {
                $this->init();
            }

            public function init()
            {
                $this->sheets = [
                    'Sheet1' => new class implements ToArray
                    {
                        public $called = false;

                        public function array(array $array)
                        {
                            $this->called = true;
                        }
                    },
                    'Sheet2' => new class implements ToArray
                    {
                        public $called = false;

                        public function array(array $array)
                        {
                            $this->called = true;
                        }
                    },
                ];
            }

            /**
             * @return array
             */
            public function conditionalSheets(): array
            {
                return $this->sheets;
            }
        };

        $import->onlySheets('Sheet1')->import('import-multiple-sheets.xlsx');
        $this->assertTrue($import->sheets['Sheet1']->called);
        $this->assertFalse($import->sheets['Sheet2']->called);

        $import->init();

        $import->onlySheets('Sheet2')->import('import-multiple-sheets.xlsx');
        $this->assertTrue($import->sheets['Sheet2']->called);
        $this->assertFalse($import->sheets['Sheet1']->called);

        $import->init();

        $import->onlySheets(['Sheet1', 'Sheet2'])->import('import-multiple-sheets.xlsx');
        $this->assertTrue($import->sheets['Sheet1']->called);
        $this->assertTrue($import->sheets['Sheet2']->called);

        $import->init();

        $import->onlySheets('Sheet1', 'Sheet2')->import('import-multiple-sheets.xlsx');
        $this->assertTrue($import->sheets['Sheet1']->called);
        $this->assertTrue($import->sheets['Sheet2']->called);
    }
}
