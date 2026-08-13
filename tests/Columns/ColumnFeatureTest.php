<?php

declare(strict_types=1);

namespace Maatwebsite\Excel\Tests\Columns;

use Carbon\Carbon;
use Carbon\CarbonImmutable;
use Illuminate\Support\Facades\Storage;
use Iterator;
use Maatwebsite\Excel\Columns\Boolean;
use Maatwebsite\Excel\Columns\Column;
use Maatwebsite\Excel\Columns\ColumnCollection;
use Maatwebsite\Excel\Columns\Date;
use Maatwebsite\Excel\Columns\DateTime;
use Maatwebsite\Excel\Columns\Decimal;
use Maatwebsite\Excel\Columns\EmptyCell;
use Maatwebsite\Excel\Columns\Formula;
use Maatwebsite\Excel\Columns\Hyperlink;
use Maatwebsite\Excel\Columns\Image;
use Maatwebsite\Excel\Columns\Number;
use Maatwebsite\Excel\Columns\Percentage;
use Maatwebsite\Excel\Columns\Price;
use Maatwebsite\Excel\Columns\RichText;
use Maatwebsite\Excel\Columns\Text;
use Maatwebsite\Excel\Tests\Data\Stubs\Database\Group;
use Maatwebsite\Excel\Tests\Data\Stubs\Database\User;
use Maatwebsite\Excel\Tests\TestCase;
use PhpOffice\PhpSpreadsheet\Cell\Cell;
use PhpOffice\PhpSpreadsheet\Cell\DataType;
use PhpOffice\PhpSpreadsheet\IOFactory;
use PhpOffice\PhpSpreadsheet\Shared\Date as ExcelDate;
use PhpOffice\PhpSpreadsheet\Spreadsheet;
use PhpOffice\PhpSpreadsheet\Worksheet\AutoFilter\Column as FilterColumn;
use PhpOffice\PhpSpreadsheet\Worksheet\Drawing;
use PHPUnit\Framework\Attributes\DataProvider;

final class ColumnFeatureTest extends TestCase
{
    #[DataProvider('readColumnTypes')]
    public function test_can_read_a_cell_based_on_column_type(Column $column, string $coordinate, mixed $expectedValue): void
    {
        $read = $this->read(__DIR__ . '/../Data/Disks/Local/columns.xlsx', 'Xlsx');
        $cell = $read->getActiveSheet()->getCell($coordinate);

        $this->assertEquals($expectedValue, $column->read($cell));
    }

    /**
     * @return Iterator<int<0, max>, array{Column, string, mixed}>
     */
    public static function readColumnTypes(): Iterator
    {
        yield [Boolean::make('Boolean'), 'A2', true];
        yield [Boolean::make('Boolean'), 'A3', false];
        yield [Boolean::make('Boolean'), 'A4', true];
        yield [Boolean::make('Boolean'), 'A5', false];
        yield [Boolean::make('Boolean'), 'A6', true];
        yield [Boolean::make('Boolean'), 'A7', false];
        yield [Boolean::make('Boolean'), 'A8', true];
        yield [Boolean::make('Boolean'), 'A9', false];
        yield [Column::make('Boolean')->type(DataType::TYPE_BOOL), 'A2', true];
        yield [Date::make('Date'), 'B2', Carbon::parse('2020-01-02')];
        yield [DateTime::make('Date'), 'B3', Carbon::parse('2020-01-02 07:00:00')];
        yield [EmptyCell::make('Empty'), 'C2', null];
        yield [Formula::make('Formula'), 'D2', '=1+1'];
        yield [Formula::make('Formula')->calculated(), 'D2', 2];
        yield [Number::make('Formula'), 'D2', 2];
        yield [Number::make('Number'), 'E2', 10];
        yield [Number::make('Number')->withDecimals(), 'E2', 10.0];
        yield [Number::make('Number')->withDecimals(), 'E3', 10.5];
        yield [Number::make('Number')->withDecimals(), 'E4', 10.5];
        yield [Number::make('Number')->withDecimals(), 'E5', 10.5];
        yield [Decimal::make('Number'), 'E2', 10.0];
        yield [Decimal::make('Number'), 'E3', 10.5];
        yield [Price::make('Number'), 'F2', 10.5];
        yield [Price::make('Number'), 'F3', 10.5];
        yield [Price::make('Number'), 'F4', 10.5];
        yield [
            RichText::make('RichText'),
            'G2',
            'test <span style="font-weight:bold; color:#000000; font-family:\'Calibri\'; font-size:12pt">test</span><span style="color:#000000; font-family:\'Calibri\'; font-size:12pt"> test</span>',
        ];
        yield [Text::make('RichText'), 'G2', 'test test test'];
        yield [Text::make('RichText'), 'H2', 'normal text'];
        yield [Percentage::make('Percentage'), 'I2', 0.1];
    }

    #[DataProvider('writeColumnTypes')]
    public function test_can_write_a_cell_based_on_column_type(Column $column, mixed $givenValue, string $dataType, mixed $expectedValue): void
    {
        $file = __DIR__ . '/../Data/Disks/Local/columns_export.xlsx';
        copy(__DIR__ . '/../Data/Disks/Local/empty-worksheet.xlsx', $file);

        $spreadsheet = $this->read($file, 'Xlsx');
        $sheet       = $spreadsheet->getActiveSheet();

        $calledWritingCallback = false;
        $column->index(1)->writing(function (Cell $cell) use (&$calledWritingCallback): void {
            $calledWritingCallback = true;
        });

        $column->beforeWriting($sheet);

        // Write value to A1
        $cell = $column->write($sheet, 1, ['attribute' => $givenValue]);

        $this->assertTrue($calledWritingCallback);

        $column->afterWriting($sheet);

        // Internal type and value are correct
        $this->assertSame($dataType, $cell->getDataType());

        $writer = IOFactory::createWriter($spreadsheet, 'Xlsx');
        $writer->save($file);

        $spreadsheet = $this->read($file, 'Xlsx');
        $cell        = $spreadsheet->getActiveSheet()->getCell('A1');

        // Written type and value are correct
        $this->assertSame($dataType, $cell->getDataType());
        $this->assertEquals($expectedValue, $cell->getValue());

        unlink($file);
    }

    /**
     * @return Iterator<int<0, max>, array{Column, mixed, string, mixed}>
     */
    public static function writeColumnTypes(): Iterator
    {
        $time = CarbonImmutable::parse('2020-01-02 07:00:00');
        yield [Boolean::make('Attribute'), true, DataType::TYPE_BOOL, true];
        yield [Boolean::make('Attribute'), false, DataType::TYPE_BOOL, false];
        yield [Boolean::make('Attribute'), 1, DataType::TYPE_BOOL, true];
        yield [Boolean::make('Attribute'), 0, DataType::TYPE_BOOL, false];
        yield [Boolean::make('Attribute'), '1', DataType::TYPE_BOOL, true];
        yield [Boolean::make('Attribute'), '0', DataType::TYPE_BOOL, false];
        yield [Boolean::make('Attribute'), 'TRUE', DataType::TYPE_BOOL, true];
        yield [Boolean::make('Attribute'), 'FALSE', DataType::TYPE_BOOL, false];
        yield [Date::make('Attribute'), '2020-01-02', DataType::TYPE_NUMERIC, ExcelDate::stringToExcel('2020-01-02')];
        yield [Date::make('Attribute'), Carbon::parse('2020-01-02'), DataType::TYPE_NUMERIC, ExcelDate::stringToExcel('2020-01-02')];
        yield [DateTime::make('Attribute'), '2020-01-02', DataType::TYPE_NUMERIC, ExcelDate::stringToExcel('2020-01-02')];
        yield [DateTime::make('Attribute'), $time, DataType::TYPE_NUMERIC, ExcelDate::dateTimeToExcel($time)];
        yield [EmptyCell::make('Attribute'), null, DataType::TYPE_NULL, null];
        yield [EmptyCell::make('Attribute'), '', DataType::TYPE_NULL, null];
        yield [EmptyCell::make('Attribute'), ' ', DataType::TYPE_NULL, null];
        yield [Formula::make('Attribute'), '=1+1', DataType::TYPE_FORMULA, '=1+1'];
        yield [Number::make('Attribute'), 10, DataType::TYPE_NUMERIC, 10];
        yield [Number::make('Attribute'), '10', DataType::TYPE_NUMERIC, 10];
        yield [Number::make('Attribute'), '10.50', DataType::TYPE_NUMERIC, 10];
        yield [Number::make('Attribute')->withDecimals(), '10.50', DataType::TYPE_NUMERIC, 10.5];
        yield [Decimal::make('Attribute'), '10.50', DataType::TYPE_NUMERIC, 10.5];
        yield [Price::make('Attribute'), '10.50', DataType::TYPE_NUMERIC, 10.5];
        yield [Price::make('Attribute')->inEuros(), '10.50', DataType::TYPE_NUMERIC, 10.5];
        yield [Price::make('Attribute')->inDollars(), '10.50', DataType::TYPE_NUMERIC, 10.5];
        yield [RichText::make('Attribute'), 'test <strong>test</strong> test', DataType::TYPE_INLINE, 'test test test'];
        yield [Text::make('Attribute'), 'text', DataType::TYPE_STRING, 'text'];
        yield [Column::make('Attribute')->type(DataType::TYPE_NUMERIC), 10.50, DataType::TYPE_NUMERIC, 10.50];
        yield [Percentage::make('Attribute'), 0.1, DataType::TYPE_NUMERIC, 0.1];
    }

    public function test_can_write_column_with_styling(): void
    {
        $sheet = (new Spreadsheet)->getActiveSheet();

        $column = Column::make('Attribute')
            ->index(1)
            ->style([
                'font' => [
                    'name' => 'Times New Roman',
                ],
            ])
            ->bold()
            ->italic()
            ->textSize(16);

        $column->beforeWriting($sheet);
        $cell = $column->write($sheet, 1, ['attribute' => 'test']);

        $this->assertSame('Times New Roman', $cell->getStyle()->getFont()->getName());
        $this->assertTrue($cell->getStyle()->getFont()->getBold());
        $this->assertTrue($cell->getStyle()->getFont()->getItalic());
        $this->assertEquals(16, $cell->getStyle()->getFont()->getSize());
    }

    public function test_can_write_column_with_image(): void
    {
        $sheet = (new Spreadsheet)->getActiveSheet();

        Storage::disk('local')->delete('avatar.jpg');
        Storage::disk('local')->copy('icon.jpg', 'avatar.jpg');
        $filepath = Storage::disk('local')->path('avatar.jpg');

        $column = Image::make('Logo', fn (): string => $filepath)
            ->height(61.0)
            ->width(100);

        $column->beforeWriting($sheet);
        $column->write($sheet, 1, []);

        $drawing = $sheet->getDrawingCollection()[0];

        $this->assertInstanceOf(Drawing::class, $drawing);
        $this->assertSame('avatar.jpg', $drawing->getFilename());
        $this->assertEquals($filepath, $drawing->getPath());
        $this->assertEqualsWithDelta(61.0, $drawing->getHeight(), PHP_FLOAT_EPSILON);
        $this->assertSame(100, $drawing->getWidth());
    }

    public function test_can_write_models_to_column(): void
    {
        $file = __DIR__ . '/../Data/Disks/Local/columns_export.xlsx';
        copy(__DIR__ . '/../Data/Disks/Local/empty-worksheet.xlsx', $file);

        $spreadsheet = new Spreadsheet;
        $sheet       = $spreadsheet->getActiveSheet();

        $user = new User(['name' => 'Patrick']);
        $user->setRelation('group', new Group(['name' => 'Admin']));

        Column::make('Name')->column('A')->write($sheet, 1, $user);
        Column::make('Group', 'group.name')->column('B')->write($sheet, 1, $user);
        Column::make('Name And Group', fn (User $user): string => $user->name . ' - ' . $user->group->name)
            ->column('C')
            ->write($sheet, 1, $user);

        IOFactory::createWriter($spreadsheet, 'Xlsx')->save($file);

        $spreadsheet = $this->read($file, 'Xlsx');

        $this->assertEquals('Patrick', $spreadsheet->getActiveSheet()->getCell('A1')->getValue());
        $this->assertEquals('Admin', $spreadsheet->getActiveSheet()->getCell('B1')->getValue());
        $this->assertEquals('Patrick - Admin', $spreadsheet->getActiveSheet()->getCell('C1')->getValue());

        unlink($file);
    }

    public function test_can_write_hyperlink(): void
    {
        $file = __DIR__ . '/../Data/Disks/Local/columns_export.xlsx';
        copy(__DIR__ . '/../Data/Disks/Local/empty-worksheet.xlsx', $file);

        $spreadsheet = new Spreadsheet;
        $sheet       = $spreadsheet->getActiveSheet();

        $column = Hyperlink::make('Name')
            ->url(fn (array $data): string => $data['link'])
            ->tooltip('Open link');

        $column->column('A')->write($sheet, 1, [
            'name' => 'Maatwebsite',
            'link' => 'https://maatwebsite.com',
        ]);

        IOFactory::createWriter($spreadsheet, 'Xlsx')->save($file);

        $spreadsheet = $this->read($file, 'Xlsx');
        $a1          = $spreadsheet->getActiveSheet()->getCell('A1');

        $this->assertEquals('Maatwebsite', $a1->getValue());
        $this->assertSame('https://maatwebsite.com', $a1->getHyperlink()->getUrl());
        $this->assertSame('Open link', $a1->getHyperlink()->getTooltip());

        unlink($file);
    }

    public function test_can_size_a_column(): void
    {
        $sheet = (new Spreadsheet)->getActiveSheet();

        $column = Column::make('Attribute')
            ->index(1)
            ->width(50);

        $column->afterWriting($sheet);
        $column->write($sheet, 1, ['attribute' => 'test']);

        $this->assertEquals(50, $sheet->getColumnDimension('A')->getWidth());
    }

    public function test_can_autosize_a_column(): void
    {
        $sheet = (new Spreadsheet)->getActiveSheet();

        $column = Column::make('Attribute')
            ->index(1)
            ->autoSize();

        $column->afterWriting($sheet);
        $column->write($sheet, 1, ['attribute' => 'aaaaaaaaaaaaaaa']);

        $this->assertSame(-1.0, $sheet->getColumnDimension('A')->getWidth());
        $this->assertTrue($sheet->getColumnDimension('A')->getAutoSize());
    }

    public function test_can_add_auto_filter(): void
    {
        $sheet = (new Spreadsheet)->getActiveSheet();

        $this->assertEmpty($sheet->getAutoFilter()->getRange());

        $column = Column::make('Attribute')
            ->index(1)
            ->autoFilter();

        ColumnCollection::make([
            $column,
            Column::make('Attribute2')
                ->index(2)
                ->autoFilter(),
            Column::make('Attribute3')
                ->index(3),
        ])->afterWriting($sheet);

        $column->write($sheet, 1, ['attribute' => 'test']);

        $this->assertSame('A1:B1', $sheet->getAutoFilter()->getRange());
        $this->assertSame(FilterColumn::AUTOFILTER_FILTERTYPE_FILTER, $sheet->getAutoFilter()->getColumn('A')->getFilterType());
    }
}
