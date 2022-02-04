<?php

namespace Maatwebsite\Excel\Tests\Columns;

use Maatwebsite\Excel\Columns\Column;
use Maatwebsite\Excel\Tests\TestCase;
use PhpOffice\PhpSpreadsheet\Spreadsheet;
use PhpOffice\PhpSpreadsheet\Style\NumberFormat;
use PhpOffice\PhpSpreadsheet\Worksheet\Worksheet;

abstract class BaseColumnTest extends TestCase
{
    /**
     * @var Worksheet
     */
    protected $sheet;

    protected function setUp(): void
    {
        parent::setUp();

        $this->sheet = $this->givenSheet();
    }

    protected function givenSheet(): Worksheet
    {
        return (new Spreadsheet())->getActiveSheet();
    }

    protected function write(Column $column, array $values): void
    {
        $column->index(0);
        $column->beforeWriting($this->sheet);
        $column->write($this->sheet, 1, $values);
        $column->afterWriting($this->sheet);
    }

    /**
     * @param mixed $value
     */
    protected function givenCellValue($value, string $dataType, string $numberFormat = NumberFormat::FORMAT_GENERAL): void
    {
        $this->sheet
            ->getCellByColumnAndRow(0, 1)
            ->setValueExplicit($value, $dataType)
            ->getStyle()->getNumberFormat()->setFormatCode($numberFormat);
    }

    /**
     * @return mixed
     */
    protected function readCellValue(Column $column)
    {
        $column->index(0);

        return $column->read(
            $this->sheet->getCellByColumnAndRow(0, 1)
        );
    }

    /**
     * @param mixed $expected
     */
    protected function assertCellValue($expected)
    {
        $this->assertSame($expected, $this->sheet->getCellByColumnAndRow(0, 1)->getValue());
    }

    protected function assertCellDataType(string $dataType)
    {
        $this->assertSame($dataType, $this->sheet->getCellByColumnAndRow(0, 1)->getDataType());
    }

    protected function assertNumberFormat(string $numberFormat)
    {
        $this->assertSame($numberFormat, $this->sheet->getCellByColumnAndRow(0, 1)->getStyle()->getNumberFormat()->getFormatCode());
    }
}