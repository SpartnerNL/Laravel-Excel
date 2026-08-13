<?php

declare(strict_types=1);

namespace Maatwebsite\Excel\Tests\Columns;

use Maatwebsite\Excel\Columns\Column;
use PhpOffice\PhpSpreadsheet\Cell\DataType;

final class ColumnTest extends BaseColumnTestCase
{
    public function test_can_write_a_nullable_column(): void
    {
        $this->write(Column::make('Column')->nullable(), [
            'column' => null,
        ]);

        $this->assertCellValue(null);
        $this->assertCellDataType(DataType::TYPE_NULL);

        $this->write(Column::make('Column')->nullable(), [
            'column' => 'Text',
        ]);

        $this->assertCellValue('Text');
        $this->assertCellDataType(DataType::TYPE_STRING);
    }

    public function test_can_read_nullable_columns(): void
    {
        $this->givenCellValue(null, DataType::TYPE_NULL);

        $this->assertNull($this->readCellValue(
            Column::make('Text')->nullable()
        ));

        $this->givenCellValue('', DataType::TYPE_STRING);

        $this->assertNull($this->readCellValue(
            Column::make('Text')->nullable()
        ));

        $this->givenCellValue('Text', DataType::TYPE_STRING);

        $this->assertSame('Text', $this->readCellValue(
            Column::make('Text')->nullable()
        ));
    }
}
