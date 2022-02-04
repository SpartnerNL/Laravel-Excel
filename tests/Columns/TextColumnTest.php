<?php

namespace Maatwebsite\Excel\Tests\Columns;

use Maatwebsite\Excel\Columns\Text;
use PhpOffice\PhpSpreadsheet\Cell\DataType;
use PhpOffice\PhpSpreadsheet\Style\NumberFormat;

class TextColumnTest extends BaseColumnTest
{
    /**
     * @param mixed $given
     *
     * @test
     * @dataProvider exportValues
     */
    public function can_write_column_values_explicitly($given, string $expected)
    {
        $this->write(Text::make('Text Column'), [
            'text_column' => $given
        ]);

        $this->assertCellValue($expected);
        $this->assertCellDataType(DataType::TYPE_STRING);
        $this->assertNumberFormat(NumberFormat::FORMAT_TEXT);
    }

    public function exportValues(): array
    {
        return [
            'Regular text' => ['Patrick', 'Patrick'],
            'Integer'      => [10, '10'],
            'Float'        => [10.111111111111, '10.111111111111'],
            'Phone number' => ['05345700755', '05345700755'],
        ];
    }

    /**
     * @param mixed $given
     *
     * @test
     * @dataProvider importValues
     */
    public function can_read_column_values_explicitly($given, string $givenDataType, string $expected)
    {
        $this->givenCellValue($given, $givenDataType);

        $value = $this->readCellValue(
            Text::make('Text Column')
        );

        $this->assertSame($expected, $value);
    }

    public function importValues(): array
    {
        return [
            'Regular text' => ['Patrick', DataType::TYPE_STRING, 'Patrick'],
            'Integer'      => [10, DataType::TYPE_NUMERIC, '10'],
            'Float'        => [10.111111111111, DataType::TYPE_NUMERIC, '10.111111111111'],
            'Phone number' => ['05345700755', DataType::TYPE_STRING, '05345700755'],
            'Empty'        => [null, DataType::TYPE_NULL, ''],
        ];
    }
}