<?php

namespace Maatwebsite\Excel\Tests\Columns;

use Maatwebsite\Excel\Columns\Number;
use PhpOffice\PhpSpreadsheet\Cell\DataType;
use PhpOffice\PhpSpreadsheet\Style\NumberFormat;

class NumberColumnTestCase extends BaseColumnTestCase
{
    /**
     * @param  mixed  $given
     *
     * @test
     *
     * @dataProvider exportValues
     */
    public function can_write_column_values_explicitly($given, $expected)
    {
        $this->write(Number::make('Number Column'), [
            'number_column' => $given,
        ]);

        $this->assertCellValue($expected);
        $this->assertCellDataType(DataType::TYPE_NUMERIC);
        $this->assertNumberFormat(NumberFormat::FORMAT_NUMBER);
    }

    public static function exportValues(): array
    {
        return [
            [null, 0],
            [10, 10],
            ['10', 10],
            ['10.50', 10],
        ];
    }

    /**
     * @param  mixed  $given
     *
     * @test
     *
     * @dataProvider decimalValues
     */
    public function can_export_number_with_decimals($given, $expected)
    {
        $this->write(Number::make('Number Column')->withDecimals(), [
            'number_column' => $given,
        ]);

        $this->assertCellValue($expected);
        $this->assertCellDataType(DataType::TYPE_NUMERIC);
        $this->assertNumberFormat(NumberFormat::FORMAT_NUMBER_00);
    }

    public static function decimalValues(): array
    {
        return [
            [null, 0.0],
            [10, 10.0],
            ['10', 10.0],
            ['10.50', 10.50],
        ];
    }

    /**
     * @param  mixed  $given
     *
     * @test
     *
     * @dataProvider importValues
     */
    public function can_read_column_values_explicitly($given, string $givenDataType, string $numberFormat, int $expected)
    {
        $this->givenCellValue($given, $givenDataType, $numberFormat);

        $value = $this->readCellValue(
            Number::make('Text Column')
        );

        $this->assertSame($expected, $value);
    }

    public static function importValues(): array
    {
        return [
            [null, DataType::TYPE_NULL, NumberFormat::FORMAT_GENERAL, 0],
            [10, DataType::TYPE_NUMERIC, NumberFormat::FORMAT_TEXT, 10],
            ['10', DataType::TYPE_STRING, NumberFormat::FORMAT_NUMBER, 10],
            ['10.50', DataType::TYPE_STRING, NumberFormat::FORMAT_TEXT, 10],
            [10.50, DataType::TYPE_NUMERIC, NumberFormat::FORMAT_NUMBER_00, 10],
        ];
    }

    /**
     * @param  mixed  $given
     *
     * @test
     *
     * @dataProvider importDecimalValues
     */
    public function can_read_column_values_with_decimals($given, string $givenDataType, string $numberFormat, float $expected)
    {
        $this->givenCellValue($given, $givenDataType, $numberFormat);

        $value = $this->readCellValue(
            Number::make('Text Column')->withDecimals()
        );

        $this->assertSame($expected, $value);
    }

    public static function importDecimalValues(): array
    {
        return [
            [null, DataType::TYPE_NULL, NumberFormat::FORMAT_GENERAL, 0.0],
            [10, DataType::TYPE_NUMERIC, NumberFormat::FORMAT_TEXT, 10.0],
            ['10', DataType::TYPE_STRING, NumberFormat::FORMAT_NUMBER, 10.0],
            ['10.50', DataType::TYPE_STRING, NumberFormat::FORMAT_TEXT, 10.5],
            [10.50, DataType::TYPE_NUMERIC, NumberFormat::FORMAT_NUMBER_00, 10.5],
        ];
    }
}
