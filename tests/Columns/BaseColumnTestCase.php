<?php

declare(strict_types=1);

namespace Maatwebsite\Excel\Tests\Columns;

use Maatwebsite\Excel\Columns\Column;
use Maatwebsite\Excel\Tests\TestCase;
use PhpOffice\PhpSpreadsheet\Spreadsheet;
use PhpOffice\PhpSpreadsheet\Style\NumberFormat;
use PhpOffice\PhpSpreadsheet\Worksheet\Worksheet;

abstract class BaseColumnTestCase extends TestCase
{
    protected Worksheet $sheet;

    protected function setUp(): void
    {
        parent::setUp();

        $this->sheet = $this->givenSheet();
    }

    protected function givenSheet(): Worksheet
    {
        return (new Spreadsheet)->getActiveSheet();
    }

    /**
     * @param  array<array-key, mixed>  $values
     */
    protected function write(Column $column, array $values): void
    {
        $column->index(1);
        $column->beforeWriting($this->sheet);
        $column->write($this->sheet, 1, $values);
        $column->afterWriting($this->sheet);
    }

    protected function givenCellValue(mixed $value, string $dataType, string $numberFormat = NumberFormat::FORMAT_GENERAL): void
    {
        $this->sheet
            ->getCell('A1')
            ->setValueExplicit($value, $dataType)
            ->getStyle()->getNumberFormat()->setFormatCode($numberFormat);
    }

    protected function readCellValue(Column $column): mixed
    {
        $column->index(1);

        return $column->read($this->sheet->getCell('A1'));
    }

    protected function assertCellValue(mixed $expected): void
    {
        $this->assertSame($expected, $this->sheet->getCell('A1')->getValue());
    }

    protected function assertCellDataType(string $dataType): void
    {
        $this->assertSame($dataType, $this->sheet->getCell('A1')->getDataType());
    }

    protected function assertNumberFormat(string $numberFormat): void
    {
        $this->assertSame($numberFormat, $this->sheet->getCell('A1')->getStyle()->getNumberFormat()->getFormatCode());
    }
}
