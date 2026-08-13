<?php

declare(strict_types=1);

namespace Maatwebsite\Excel\Tests\Columns;

use Iterator;
use Maatwebsite\Excel\Columns\Number;
use PhpOffice\PhpSpreadsheet\Cell\DataType;
use PhpOffice\PhpSpreadsheet\Style\NumberFormat;
use PHPUnit\Framework\Attributes\DataProvider;

final class NumberColumnTest extends BaseColumnTestCase
{
    #[DataProvider('exportValues')]
    public function test_can_write_column_values_explicitly(mixed $given, int $expected): void
    {
        $this->write(Number::make('Number Column'), [
            'number_column' => $given,
        ]);

        $this->assertCellValue($expected);
        $this->assertCellDataType(DataType::TYPE_NUMERIC);
        $this->assertNumberFormat(NumberFormat::FORMAT_NUMBER);
    }

    /**
     * @return Iterator<int<0, max>, array{mixed, int}>
     */
    public static function exportValues(): Iterator
    {
        yield [null, 0];
        yield [10, 10];
        yield ['10', 10];
        yield ['10.50', 10];
    }

    #[DataProvider('decimalValues')]
    public function test_can_export_number_with_decimals(mixed $given, float $expected): void
    {
        $this->write(Number::make('Number Column')->withDecimals(), [
            'number_column' => $given,
        ]);

        $this->assertCellValue($expected);
        $this->assertCellDataType(DataType::TYPE_NUMERIC);
        $this->assertNumberFormat(NumberFormat::FORMAT_NUMBER_00);
    }

    /**
     * @return Iterator<int<0, max>, array{mixed, float}>
     */
    public static function decimalValues(): Iterator
    {
        yield [null, 0.0];
        yield [10, 10.0];
        yield ['10', 10.0];
        yield ['10.50', 10.50];
    }

    #[DataProvider('importValues')]
    public function test_can_read_column_values_explicitly(mixed $given, string $givenDataType, string $numberFormat, int $expected): void
    {
        $this->givenCellValue($given, $givenDataType, $numberFormat);

        $this->assertSame($expected, $this->readCellValue(
            Number::make('Number Column')
        ));
    }

    /**
     * @return Iterator<int<0, max>, array{mixed, string, string, int}>
     */
    public static function importValues(): Iterator
    {
        yield [null, DataType::TYPE_NULL, NumberFormat::FORMAT_GENERAL, 0];
        yield [10, DataType::TYPE_NUMERIC, NumberFormat::FORMAT_TEXT, 10];
        yield ['10', DataType::TYPE_STRING, NumberFormat::FORMAT_NUMBER, 10];
        yield ['10.50', DataType::TYPE_STRING, NumberFormat::FORMAT_TEXT, 10];
        yield [10.50, DataType::TYPE_NUMERIC, NumberFormat::FORMAT_NUMBER_00, 10];
    }

    #[DataProvider('importDecimalValues')]
    public function test_can_read_column_values_with_decimals(mixed $given, string $givenDataType, string $numberFormat, float $expected): void
    {
        $this->givenCellValue($given, $givenDataType, $numberFormat);

        $this->assertSame($expected, $this->readCellValue(
            Number::make('Number Column')->withDecimals()
        ));
    }

    /**
     * @return Iterator<int<0, max>, array{mixed, string, string, float}>
     */
    public static function importDecimalValues(): Iterator
    {
        yield [null, DataType::TYPE_NULL, NumberFormat::FORMAT_GENERAL, 0.0];
        yield [10, DataType::TYPE_NUMERIC, NumberFormat::FORMAT_TEXT, 10.0];
        yield ['10', DataType::TYPE_STRING, NumberFormat::FORMAT_NUMBER, 10.0];
        yield ['10.50', DataType::TYPE_STRING, NumberFormat::FORMAT_TEXT, 10.5];
        yield [10.50, DataType::TYPE_NUMERIC, NumberFormat::FORMAT_NUMBER_00, 10.5];
    }
}
