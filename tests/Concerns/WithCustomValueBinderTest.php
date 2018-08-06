<?php

namespace Maatwebsite\Excel\Tests\Concerns;

use Maatwebsite\Excel\Excel;
use Maatwebsite\Excel\Tests\TestCase;
use PhpOffice\PhpSpreadsheet\Cell\Cell;
use Maatwebsite\Excel\Concerns\WithCustomValueBinder;

class WithCustomValueBinderTest extends TestCase
{
    /**
     * @test
     */
    public function can_set_a_value_binder()
    {
        $import = new Class implements WithCustomValueBinder {
            public function bindValue(Cell $cell, $value) {}
        };

        $reader = $this->app->make(Excel::class)->import($import, 'import.xlsx');

        $this->assertSame(
            $import,
            $reader->getDelegate()->getActiveSheet()->getCell('A1')->getValueBinder()
        );
    }
}
