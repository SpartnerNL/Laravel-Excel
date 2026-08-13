<?php

declare(strict_types=1);

namespace Maatwebsite\Excel\Tests\Columns;

use Iterator;
use Maatwebsite\Excel\Columns\Text;
use PhpOffice\PhpSpreadsheet\Cell\DataType;
use PhpOffice\PhpSpreadsheet\Style\NumberFormat;
use PHPUnit\Framework\Attributes\DataProvider;

final class TextColumnTest extends BaseColumnTestCase
{
    #[DataProvider('exportValues')]
    public function test_can_write_column_values_explicitly(mixed $given, string $expected): void
    {
        $this->write(Text::make('Text Column'), [
            'text_column' => $given,
        ]);

        $this->assertCellValue($expected);
        $this->assertCellDataType(DataType::TYPE_STRING);
        $this->assertNumberFormat(NumberFormat::FORMAT_TEXT);
    }

    /**
     * @return Iterator<string, array{mixed, string}>
     */
    public static function exportValues(): Iterator
    {
        yield 'Regular text' => ['Patrick', 'Patrick'];
        yield 'Integer' => [10, '10'];
        yield 'Float' => [10.111111111111, '10.111111111111'];
        yield 'Phone number' => ['05345700755', '05345700755'];
    }

    #[DataProvider('importValues')]
    public function test_can_read_column_values_explicitly(mixed $given, string $givenDataType, string $expected): void
    {
        $this->givenCellValue($given, $givenDataType);

        $this->assertSame($expected, $this->readCellValue(
            Text::make('Text Column')
        ));
    }

    /**
     * @return Iterator<string, array{mixed, string, string}>
     */
    public static function importValues(): Iterator
    {
        yield 'Regular text' => ['Patrick', DataType::TYPE_STRING, 'Patrick'];
        yield 'Integer' => [10, DataType::TYPE_NUMERIC, '10'];
        yield 'Float' => [10.111111111111, DataType::TYPE_NUMERIC, '10.111111111111'];
        yield 'Phone number' => ['05345700755', DataType::TYPE_STRING, '05345700755'];
        yield 'Empty' => [null, DataType::TYPE_NULL, ''];
    }
}
