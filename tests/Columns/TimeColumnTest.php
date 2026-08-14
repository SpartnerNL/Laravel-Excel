<?php

declare(strict_types=1);

namespace Maatwebsite\Excel\Tests\Columns;

use Carbon\Carbon;
use Iterator;
use Maatwebsite\Excel\Columns\Time;
use PhpOffice\PhpSpreadsheet\Cell\DataType;
use PhpOffice\PhpSpreadsheet\Shared\Date as ExcelDate;
use PhpOffice\PhpSpreadsheet\Style\NumberFormat;
use PHPUnit\Framework\Attributes\DataProvider;

final class TimeColumnTest extends BaseColumnTestCase
{
    #[DataProvider('exportValues')]
    public function test_can_write_time_values(mixed $given, float $expected): void
    {
        $this->write(Time::make('Shift Start'), [
            'shift_start' => $given,
        ]);

        $this->assertCellValue($expected);
        $this->assertCellDataType(DataType::TYPE_NUMERIC);
        $this->assertNumberFormat(NumberFormat::FORMAT_DATE_TIME4);
    }

    /**
     * @return Iterator<int<0, max>, array{mixed, float}>
     */
    public static function exportValues(): Iterator
    {
        yield [Carbon::createFromTime(9, 30, 0), ExcelDate::dateTimeToExcel(Carbon::createFromTime(9, 30, 0))];
        yield [Carbon::createFromTime(17, 0, 0), ExcelDate::dateTimeToExcel(Carbon::createFromTime(17, 0, 0))];
    }

    #[DataProvider('importValues')]
    public function test_can_read_time_values(float $given, int $expectedHour, int $expectedMinute, int $expectedSecond): void
    {
        $this->givenCellValue($given, DataType::TYPE_NUMERIC, NumberFormat::FORMAT_DATE_TIME4);

        $result = $this->readCellValue(Time::make('Shift Start'));

        $this->assertInstanceOf(Carbon::class, $result);
        $this->assertSame($expectedHour, $result->hour);
        $this->assertSame($expectedMinute, $result->minute);
        $this->assertSame($expectedSecond, $result->second);
    }

    /**
     * @return Iterator<int<0, max>, array{float, int, int, int}>
     */
    public static function importValues(): Iterator
    {
        yield [ExcelDate::dateTimeToExcel(Carbon::createFromTime(9, 30, 0)), 9, 30, 0];
        yield [ExcelDate::dateTimeToExcel(Carbon::createFromTime(17, 0, 0)), 17, 0, 0];
        yield [ExcelDate::dateTimeToExcel(Carbon::createFromTime(0, 0, 0)), 0, 0, 0];
    }
}
