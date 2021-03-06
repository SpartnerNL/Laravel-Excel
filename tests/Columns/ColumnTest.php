<?php

namespace Maatwebsite\Excel\Tests\Columns;

use Carbon\Carbon;
use Carbon\CarbonImmutable;
use Maatwebsite\Excel\Columns\Boolean;
use Maatwebsite\Excel\Columns\Column;
use Maatwebsite\Excel\Columns\Date;
use Maatwebsite\Excel\Columns\DateTime;
use Maatwebsite\Excel\Columns\Decimal;
use Maatwebsite\Excel\Columns\EmptyCell;
use Maatwebsite\Excel\Columns\Formula;
use Maatwebsite\Excel\Columns\Number;
use Maatwebsite\Excel\Columns\Price;
use Maatwebsite\Excel\Columns\RichText;
use Maatwebsite\Excel\Columns\Text;
use Maatwebsite\Excel\Tests\TestCase;
use PhpOffice\PhpSpreadsheet\Cell\DataType;
use PhpOffice\PhpSpreadsheet\IOFactory;
use PhpOffice\PhpSpreadsheet\Shared\Date as ExcelDate;
use PhpOffice\PhpSpreadsheet\Spreadsheet;
use PhpOffice\PhpSpreadsheet\Worksheet\AutoFilter\Column as FilterColumn;

class ColumnTest extends TestCase
{
    /**
     * @test
     * @dataProvider readColumnTypes
     */
    public function can_read_a_cell_based_on_column_type(Column $column, $coordinate, $expectedValue)
    {
        $read = $this->read(__DIR__ . '/../Data/Disks/Local/columns.xlsx', 'Xlsx');
        $cell = $read->getActiveSheet()->getCell($coordinate);

        $value = $column->read($cell);

        $this->assertEquals($expectedValue, $value);
    }

    public function readColumnTypes(): array
    {
        return [
            [Boolean::make('Boolean'), 'A2', true],
            [Boolean::make('Boolean'), 'A3', false],
            [Boolean::make('Boolean'), 'A4', true],
            [Boolean::make('Boolean'), 'A5', false],
            [Boolean::make('Boolean'), 'A6', true],
            [Boolean::make('Boolean'), 'A7', false],
            [Boolean::make('Boolean'), 'A8', true],
            [Boolean::make('Boolean'), 'A9', false],
            [Column::make('Boolean')->type(DataType::TYPE_BOOL), 'A2', true],

            [Date::make('Date'), 'B2', Carbon::parse('2020-01-02')],
            [DateTime::make('Date'), 'B3', Carbon::parse('2020-01-02 07:00:00')],

            [EmptyCell::make('Empty'), 'C2', null],

            [Formula::make('Formula'), 'D2', '=1+1'],
            [Formula::make('Formula')->calculated(), 'D2', 2],
            [Number::make('Formula'), 'D2', 2],

            [Number::make('Number'), 'E2', 10],
            [Number::make('Number')->withDecimals(), 'E2', 10.0],
            [Number::make('Number')->withDecimals(), 'E3', 10.5],
            [Number::make('Number')->withDecimals(), 'E4', 10.5],
            [Number::make('Number')->withDecimals(), 'E5', 10.5],

            [Decimal::make('Number'), 'E2', 10.0],
            [Decimal::make('Number'), 'E3', 10.5],

            [Price::make('Number'), 'F2', 10.5],
            [Price::make('Number'), 'F3', 10.5],
            [Price::make('Number'), 'F4', 10.5],

            [
                RichText::make('RichText'),
                'G2',
                'test <span style="font-weight:bold; color:#000000; font-family:\'Calibri\'; font-size:12pt">test</span><span style="color:#000000; font-family:\'Calibri\'; font-size:12pt"> test</span>'
            ],
            [Text::make('RichText'), 'G2', 'test test test'],

            [Text::make('RichText'), 'H2', 'normal text'],
        ];
    }

    /**
     * @test
     * @dataProvider writeColumnTypes
     */
    public function can_write_a_cell_based_on_column_type(Column $column, $givenValue, string $dataType, $expectedValue)
    {
        $file = __DIR__ . '/../Data/Disks/Local/columns_export.xlsx';
        copy(__DIR__ . '/../Data/Disks/Local/empty-worksheet.xlsx', $file);

        $spreadsheet = $this->read($file, 'Xlsx');
        $sheet       = $spreadsheet->getActiveSheet();

        // Write value to A1
        $cell = $column->index(1)->write($sheet, 1, ['attribute' => $givenValue]);

        // Internal type and value are correct
        $this->assertEquals($dataType, $cell->getDataType());

        $writer = IOFactory::createWriter($spreadsheet, 'Xlsx');
        $writer->save($file);

        $spreadsheet = $this->read(__DIR__ . '/../Data/Disks/Local/columns_export.xlsx', 'Xlsx');
        $cell        = $spreadsheet->getActiveSheet()->getCell('A1');

        // Written type and value are correct
        $this->assertEquals($dataType, $cell->getDataType());
        $this->assertEquals($expectedValue, $cell->getValue());

        unlink($file);
    }

    public function writeColumnTypes(): array
    {
        $time = CarbonImmutable::parse('2020-01-02 07:00:00');

        return [
            [Boolean::make('Attribute'), true, DataType::TYPE_BOOL, true],
            [Boolean::make('Attribute'), false, DataType::TYPE_BOOL, false],
            [Boolean::make('Attribute'), 1, DataType::TYPE_BOOL, true],
            [Boolean::make('Attribute'), 0, DataType::TYPE_BOOL, false],
            [Boolean::make('Attribute'), '1', DataType::TYPE_BOOL, true],
            [Boolean::make('Attribute'), '0', DataType::TYPE_BOOL, false],
            [Boolean::make('Attribute'), 'TRUE', DataType::TYPE_BOOL, true],
            [Boolean::make('Attribute'), 'FALSE', DataType::TYPE_BOOL, false],

            [Date::make('Attribute'), '2020-01-02', DataType::TYPE_NUMERIC, ExcelDate::stringToExcel('2020-01-02')],
            [Date::make('Attribute'), Carbon::parse('2020-01-02'), DataType::TYPE_NUMERIC, ExcelDate::stringToExcel('2020-01-02')],

            [DateTime::make('Attribute'), '2020-01-02', DataType::TYPE_NUMERIC, ExcelDate::stringToExcel('2020-01-02')],
            [DateTime::make('Attribute'), $time, DataType::TYPE_NUMERIC, round(ExcelDate::dateTimeToExcel($time), 9)],

            [EmptyCell::make('Attribute'), null, DataType::TYPE_NULL, null],
            [EmptyCell::make('Attribute'), '', DataType::TYPE_NULL, null],
            [EmptyCell::make('Attribute'), ' ', DataType::TYPE_NULL, null],

            [Formula::make('Attribute'), '=1+1', DataType::TYPE_FORMULA, '=1+1'],

            [Number::make('Attribute'), 10, DataType::TYPE_NUMERIC, 10],
            [Number::make('Attribute'), '10', DataType::TYPE_NUMERIC, 10],
            [Number::make('Attribute'), '10.50', DataType::TYPE_NUMERIC, 10.5],
            [Decimal::make('Attribute'), '10.50', DataType::TYPE_NUMERIC, 10.5],

            [Price::make('Attribute'), '10.50', DataType::TYPE_NUMERIC, 10.5],
            [Price::make('Attribute')->inEuros(), '10.50', DataType::TYPE_NUMERIC, 10.5],
            [Price::make('Attribute')->inDollars(), '10.50', DataType::TYPE_NUMERIC, 10.5],

            [RichText::make('Attribute'), 'test <strong>test</strong> test', DataType::TYPE_INLINE, 'test test test'],
            [Text::make('Attribute'), 'text', DataType::TYPE_STRING, 'text'],

            [Column::make('Attribute')->type(DataType::TYPE_NUMERIC), 10.50, DataType::TYPE_NUMERIC, 10.50],
        ];
    }

    /**
     * @test
     */
    public function can_write_column_with_styling()
    {
        $spreadsheet = new Spreadsheet();
        $sheet       = $spreadsheet->getActiveSheet();

        // Write value to A1
        $column = Column
            ::make('Attribute')
            ->index(1)
            ->style([
                'font' => [
                    'name' => 'Times New Roman',
                ]
            ])
            ->bold()
            ->italic()
            ->textSize(16);

        $column->beforeWriting($sheet);
        $cell = $column->write($sheet, 1, ['attribute' => 'test']);

        $this->assertEquals('Times New Roman', $cell->getStyle()->getFont()->getName());
        $this->assertTrue($cell->getStyle()->getFont()->getBold());
        $this->assertTrue($cell->getStyle()->getFont()->getItalic());
        $this->assertEquals(16, $cell->getStyle()->getFont()->getSize());
    }

    /**
     * @test
     */
    public function can_size_a_column()
    {
        $spreadsheet = new Spreadsheet();
        $sheet       = $spreadsheet->getActiveSheet();

        // Write value to A1
        $column = Column
            ::make('Attribute')
            ->index(1)
           ->width(50);

        $column->afterWriting($sheet);
        $column->write($sheet, 1, ['attribute' => 'test']);

        $this->assertEquals(50, $sheet->getColumnDimension('A')->getWidth());
    }

    /**
     * @test
     */
    public function can_autosize_a_column()
    {
        $spreadsheet = new Spreadsheet();
        $sheet       = $spreadsheet->getActiveSheet();

        // Write value to A1
        $column = Column
            ::make('Attribute')
            ->index(1)
            ->autoSize();

        $column->afterWriting($sheet);
        $column->write($sheet, 1, ['attribute' => 'aaaaaaaaaaaaaaa']);

        $this->assertEquals(-1.0, $sheet->getColumnDimension('A')->getWidth());
        $this->assertTrue($sheet->getColumnDimension('A')->getAutoSize());
    }

    /**
     * @test
     */
    public function can_add_auto_filter()
    {
        $spreadsheet = new Spreadsheet();
        $sheet       = $spreadsheet->getActiveSheet();

        $this->assertNull($sheet->getAutoFilter()->getRange());

        // Write value to A1
        $column = Column
            ::make('Attribute')
            ->index(1)
            ->autoFilter();

        $column->afterWriting($sheet);
        $column->write($sheet, 1, ['attribute' => 'test']);

        $this->assertEquals('A1:A1', $sheet->getAutoFilter()->getRange());
        $this->assertEquals(FilterColumn::AUTOFILTER_FILTERTYPE_FILTER, $sheet->getAutoFilter()->getColumn('A')->getFilterType());
    }
}
