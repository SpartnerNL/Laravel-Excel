<?php

namespace Maatwebsite\Excel\Tests\Concerns;

use Maatwebsite\Excel\Excel;
use Maatwebsite\Excel\Reader;
use Maatwebsite\Excel\Tests\TestCase;
use PhpOffice\PhpSpreadsheet\Cell\Cell;
use PhpOffice\PhpSpreadsheet\Cell\DataType;
use Maatwebsite\Excel\Concerns\WithCustomValueBinder;
use PhpOffice\PhpSpreadsheet\Cell\DefaultValueBinder;

class WithCustomValueBinderTest extends TestCase
{
    /**
     * @test
     */
    public function can_set_a_value_binder()
    {
        $import = new class extends DefaultValueBinder implements WithCustomValueBinder {
            /**
             * {@inheritdoc}
             */
            public function bindValue(Cell $cell, $value)
            {
                if ($cell->getCoordinate() === 'B2') {
                    $cell->setValueExplicit($value, DataType::TYPE_STRING);

                    return true;
                }

                if ($cell->getRow() === 3) {
                    $cell->setValueExplicit($value, DataType::TYPE_BOOL);

                    return true;
                }

                return parent::bindValue($cell, $value);
            }
        };

        /** @var Reader $reader */
        $reader = $this->app->make(Excel::class)->import($import, 'value-binder-import.xlsx');

        $this->assertSame(
            $import,
            $reader->getDelegate()->getActiveSheet()->getCell('A1')->getValueBinder()
        );

        $this->assertSame([
            [
                [
                    'col1',
                    'col2',
                ],
                [
                    1.0, // by default PhpSpreadsheet will convert numbers to float
                    '2', // Forced to be a string
                ],
                [
                    true, // Forced to be a boolean
                    true, // Forced to be a boolean
                ],
            ],
        ], $reader->toArray());
    }
}
