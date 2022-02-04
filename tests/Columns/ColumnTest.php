<?php

namespace Maatwebsite\Excel\Tests\Columns;

use Maatwebsite\Excel\Columns\Column;
use PhpOffice\PhpSpreadsheet\Cell\DataType;

class ColumnTest extends BaseColumnTest
{
    /**
     * @test
     */
    public function can_write_a_nullable_column()
    {
        /**
         * NULL VALUE.
         */
        $this->write(Column::make('Column')->nullable(), [
            'column' => null,
        ]);

        $this->assertCellValue(null);
        $this->assertCellDataType(DataType::TYPE_NULL);

        /**
         * NON-NULL VALUE.
         */
        $this->write(Column::make('Column')->nullable(), [
            'column' => 'Text',
        ]);

        $this->assertCellValue('Text');
        $this->assertCellDataType(DataType::TYPE_STRING);
    }

    /**
     * @test
     */
    public function can_read_nullable_columns()
    {
        /**
         * NULL VALUE.
         */
        $this->givenCellValue(null, DataType::TYPE_NULL);

        $value = $this->readCellValue(
            Column::make('Text')->nullable()
        );

        $this->assertSame(null, $value);

        /**
         * EMPTY CELL.
         */
        $this->givenCellValue('', DataType::TYPE_STRING);

        $value = $this->readCellValue(
            Column::make('Text')->nullable()
        );

        $this->assertSame(null, $value);

        /**
         * NON-NULL VALUE.
         */
        $this->givenCellValue('Text', DataType::TYPE_STRING);

        $value = $this->readCellValue(
            Column::make('Text')->nullable()
        );

        $this->assertSame('Text', $value);
    }
}
