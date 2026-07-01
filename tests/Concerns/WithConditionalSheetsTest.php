<?php

namespace Maatwebsite\Excel\Tests\Concerns;

use Maatwebsite\Excel\Concerns\Importable;
use Maatwebsite\Excel\Concerns\ToArray;
use Maatwebsite\Excel\Concerns\WithConditionalSheets;
use Maatwebsite\Excel\Concerns\WithMultipleSheets;
use Maatwebsite\Excel\Tests\TestCase;

class WithConditionalSheetsTest extends TestCase
{
    public function test_can_select_which_sheets_will_be_imported(): void
    {
        $import = new class implements WithMultipleSheets
        {
            use Importable, WithConditionalSheets;

            /** @var array<string, object> */
            public $sheets = [];

            public function __construct()
            {
                $this->init();
            }

            public function init(): void
            {
                $this->sheets = [
                    'Sheet1' => new class implements ToArray
                    {
                        public bool $called = false;

                        public function array(array $array): void
                        {
                            $this->called = true;
                        }
                    },
                    'Sheet2' => new class implements ToArray
                    {
                        public bool $called = false;

                        public function array(array $array): void
                        {
                            $this->called = true;
                        }
                    },
                ];
            }

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
