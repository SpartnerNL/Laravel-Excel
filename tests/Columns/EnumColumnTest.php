<?php

declare(strict_types=1);

namespace Maatwebsite\Excel\Tests\Columns;

use Maatwebsite\Excel\Columns\Enum;
use PhpOffice\PhpSpreadsheet\Cell\DataType;
use PhpOffice\PhpSpreadsheet\Style\NumberFormat;

enum UserStatus: string
{
    case Active   = 'active';
    case Inactive = 'inactive';
}

enum Priority: int
{
    case Low    = 1;
    case Medium = 2;
    case High   = 3;
}

enum Color
{
    case Red;
    case Green;
    case Blue;
}

final class EnumColumnTest extends BaseColumnTestCase
{
    public function test_writes_string_backed_enum_by_value(): void
    {
        $this->write(Enum::make('Status')->of(UserStatus::class), [
            'status' => UserStatus::Active,
        ]);

        $this->assertSame('active', $this->sheet->getCell('A1')->getValue());
    }

    public function test_writes_int_backed_enum_by_value(): void
    {
        $this->write(Enum::make('Priority')->of(Priority::class), [
            'priority' => Priority::High,
        ]);

        $this->assertSame(3, $this->sheet->getCell('A1')->getValue());
    }

    public function test_writes_unit_enum_by_name(): void
    {
        $this->write(Enum::make('Color')->of(Color::class), [
            'color' => Color::Red,
        ]);

        $this->assertSame('Red', $this->sheet->getCell('A1')->getValue());
    }

    public function test_writes_backed_enum_by_name_when_requested(): void
    {
        $this->write(Enum::make('Status')->of(UserStatus::class)->byName(), [
            'status' => UserStatus::Active,
        ]);

        $this->assertSame('Active', $this->sheet->getCell('A1')->getValue());
    }

    public function test_reads_string_backed_enum_by_value(): void
    {
        $this->givenCellValue('active', DataType::TYPE_STRING, NumberFormat::FORMAT_GENERAL);

        $result = $this->readCellValue(Enum::make('Status')->of(UserStatus::class));

        $this->assertSame(UserStatus::Active, $result);
    }

    public function test_reads_int_backed_enum_by_value(): void
    {
        $this->givenCellValue(2, DataType::TYPE_NUMERIC, NumberFormat::FORMAT_GENERAL);

        $result = $this->readCellValue(Enum::make('Priority')->of(Priority::class));

        $this->assertSame(Priority::Medium, $result);
    }

    public function test_reads_unit_enum_by_name(): void
    {
        $this->givenCellValue('Green', DataType::TYPE_STRING, NumberFormat::FORMAT_GENERAL);

        $result = $this->readCellValue(Enum::make('Color')->of(Color::class));

        $this->assertSame(Color::Green, $result);
    }

    public function test_reads_backed_enum_by_name_when_requested(): void
    {
        $this->givenCellValue('Active', DataType::TYPE_STRING, NumberFormat::FORMAT_GENERAL);

        $result = $this->readCellValue(Enum::make('Status')->of(UserStatus::class)->byName());

        $this->assertSame(UserStatus::Active, $result);
    }

    public function test_returns_null_for_unknown_backed_enum_value(): void
    {
        $this->givenCellValue('unknown', DataType::TYPE_STRING, NumberFormat::FORMAT_GENERAL);

        $result = $this->readCellValue(Enum::make('Status')->of(UserStatus::class));

        $this->assertNull($result);
    }

    public function test_returns_null_for_unknown_unit_enum_name(): void
    {
        $this->givenCellValue('Purple', DataType::TYPE_STRING, NumberFormat::FORMAT_GENERAL);

        $result = $this->readCellValue(Enum::make('Color')->of(Color::class));

        $this->assertNull($result);
    }

    public function test_returns_raw_value_when_no_enum_class_set(): void
    {
        $this->givenCellValue('active', DataType::TYPE_STRING, NumberFormat::FORMAT_GENERAL);

        $result = $this->readCellValue(Enum::make('Status'));

        $this->assertSame('active', $result);
    }
}
