<?php

declare(strict_types=1);

namespace Maatwebsite\Excel\Tests\Columns;

use Maatwebsite\Excel\Columns\CellStyle;
use Maatwebsite\Excel\Columns\Column;
use Maatwebsite\Excel\Columns\ColumnCollection;
use Maatwebsite\Excel\Columns\Hyperlink;
use Maatwebsite\Excel\Columns\Percentage;
use Maatwebsite\Excel\Columns\Text;
use Maatwebsite\Excel\ImageContent;
use Maatwebsite\Excel\Tests\TestCase;
use PhpOffice\PhpSpreadsheet\Cell\Cell;
use PhpOffice\PhpSpreadsheet\Cell\DataType;
use PhpOffice\PhpSpreadsheet\Spreadsheet;
use PhpOffice\PhpSpreadsheet\Worksheet\AutoFilter\Column as FilterColumn;
use PhpOffice\PhpSpreadsheet\Worksheet\MemoryDrawing;
use PhpOffice\PhpSpreadsheet\Worksheet\Worksheet;

final class ColumnApiTest extends TestCase
{
    public function test_can_hide_and_collapse_a_column(): void
    {
        $sheet = $this->givenSheet();

        $column = Column::make('Attribute')->index(1)->hide()->collapse();
        $column->write($sheet, 1, ['attribute' => 'test']);
        $column->afterWriting($sheet, 1);

        $this->assertFalse($sheet->getColumnDimension('A')->getVisible());
        $this->assertTrue($sheet->getColumnDimension('A')->getCollapsed());
    }

    public function test_can_set_a_font_by_name_and_size(): void
    {
        $sheet = $this->givenSheet();

        $column = Column::make('Attribute')->index(1)->font('Verdana', 14.0);
        $cell   = $column->write($sheet, 1, ['attribute' => 'test']);
        $column->afterWriting($sheet, 1);

        $this->assertSame('Verdana', $cell->getStyle()->getFont()->getName());
        $this->assertEqualsWithDelta(14.0, $cell->getStyle()->getFont()->getSize(), PHP_FLOAT_EPSILON);
    }

    public function test_repeated_style_calls_replace_rather_than_accumulate(): void
    {
        $column = Column::make('Attribute')
            ->bold()
            ->bold(false);

        // array_merge_recursive would have produced ['bold' => [true, false]].
        $this->assertSame(['font' => ['bold' => false]], $column->getStyle());
    }

    public function test_can_style_individual_cells_from_a_callback(): void
    {
        $sheet = $this->givenSheet();

        $column = Column::make('Attribute')
            ->index(1)
            ->withCellStyling(function (CellStyle $style, array $row): void {
                if ($row['attribute'] === 'danger') {
                    $style->bold();
                }
            });

        $column->write($sheet, 1, ['attribute' => 'danger']);
        $column->write($sheet, 2, ['attribute' => 'normal']);

        // Fetched one at a time: the cell collection only keeps one cell live.
        $this->assertTrue($sheet->getCell('A1')->getStyle()->getFont()->getBold());
        $this->assertFalse($sheet->getCell('A2')->getStyle()->getFont()->getBold());
    }

    public function test_can_add_a_comment_to_every_cell(): void
    {
        $sheet = $this->givenSheet();

        $column = Column::make('Attribute')
            ->index(1)
            ->comment('A note', 'Patrick');

        $column->write($sheet, 1, ['attribute' => 'test']);

        $comment = $sheet->getComment('A1');

        $this->assertSame('A note', $comment->getText()->getPlainText());
        $this->assertSame('Patrick', $comment->getAuthor());
    }

    public function test_a_comment_can_be_derived_from_the_row(): void
    {
        $sheet = $this->givenSheet();

        $column = Column::make('Attribute')
            ->index(1)
            ->comment(fn (array $row): ?string => $row['note'] ?? null);

        $column->write($sheet, 1, ['attribute' => 'test', 'note' => 'Row note']);
        $column->write($sheet, 2, ['attribute' => 'test']);

        $this->assertSame('Row note', $sheet->getComment('A1')->getText()->getPlainText());
        $this->assertSame('', $sheet->getComment('A2')->getText()->getPlainText());
    }

    public function test_auto_filter_writes_its_rules(): void
    {
        $sheet = $this->givenSheet();

        $column = Column::make('Attribute')
            ->index(1)
            ->autoFilter(['equal' => ['Patrick', 'Taylor']]);

        $column->write($sheet, 2, ['attribute' => 'Patrick']);

        ColumnCollection::make([$column])->afterWriting($sheet, 1);

        $filter = $sheet->getAutoFilter()->getColumn('A');

        $this->assertSame(FilterColumn::AUTOFILTER_FILTERTYPE_FILTER, $filter->getFilterType());
        $this->assertCount(2, $filter->getRules());
        $this->assertSame('Patrick', $filter->getRules()[0]->getValue());
        $this->assertSame('Taylor', $filter->getRules()[1]->getValue());
    }

    public function test_auto_filter_range_starts_at_the_heading_row(): void
    {
        $sheet = $this->givenSheet();

        $columns = ColumnCollection::make([
            Column::make('One')->index(1)->autoFilter(),
            Column::make('Two')->index(2)->autoFilter(),
            Column::make('Three')->index(3),
        ]);

        $columns->writeHeadings($sheet, 1);
        $columns->each(fn (Column $column): Cell => $column->write($sheet, 2, ['one' => 'a', 'two' => 'b', 'three' => 'c']));
        $columns->afterWriting($sheet, 1);

        // Only the two filtered columns, spanning heading row through last data row.
        $this->assertSame('A1:B2', $sheet->getAutoFilter()->getRange());
    }

    public function test_hyperlink_tooltip_can_be_derived_from_the_row(): void
    {
        $sheet = $this->givenSheet();

        $column = Hyperlink::make('Name')
            ->url(fn (array $row): string => $row['link'])
            ->tooltip(fn (array $row): string => 'Open ' . $row['name']);

        $cell = $column->column('A')->write($sheet, 1, [
            'name' => 'Maatwebsite',
            'link' => 'https://maatwebsite.com',
        ]);

        $this->assertSame('https://maatwebsite.com', $cell->getHyperlink()->getUrl());
        $this->assertSame('Open Maatwebsite', $cell->getHyperlink()->getTooltip());
    }

    public function test_hyperlink_can_read_a_tooltip(): void
    {
        $sheet = $this->givenSheet();
        $sheet->getCell('A1')->setValue('Maatwebsite');
        $sheet->getCell('A1')->getHyperlink()->setUrl('https://maatwebsite.com')->setTooltip('Open it');

        $column = Hyperlink::make('Tooltip')->column('A')->tooltip();

        $this->assertSame('Open it', $column->read($sheet->getCell('A1')));
        $this->assertTrue($column->needsStyleInformation());
    }

    public function test_percentage_reads_and_writes_excel_fractions_by_default(): void
    {
        $sheet = $this->givenSheet();

        $column = Percentage::make('Rate', 'rate')->index(1);
        $cell   = $column->write($sheet, 1, ['rate' => 0.1]);

        $this->assertEqualsWithDelta(0.1, $cell->getValue(), PHP_FLOAT_EPSILON);
        $this->assertEqualsWithDelta(0.1, $column->read($cell), PHP_FLOAT_EPSILON);
    }

    public function test_percentage_can_work_in_whole_numbers(): void
    {
        $sheet = $this->givenSheet();

        $column = Percentage::make('Rate', 'rate')->fromWholeNumbers()->index(1);
        $cell   = $column->write($sheet, 1, ['rate' => 10]);

        // Excel stores 10% as 0.1, but the model speaks in whole percents.
        $this->assertEqualsWithDelta(0.1, $cell->getValue(), PHP_FLOAT_EPSILON);
        $this->assertEqualsWithDelta(10.0, $column->read($cell), PHP_FLOAT_EPSILON);
    }

    public function test_image_content_exposes_its_drawing(): void
    {
        $spreadsheet = $this->read(__DIR__ . '/../Data/Disks/Local/import-users-with-columns.xlsx', 'Xlsx');
        $drawing     = $spreadsheet->getActiveSheet()->getDrawingCollection()[0];

        $content = ImageContent::from($drawing);

        $this->assertSame($drawing, $content->drawing());
        $this->assertSame('jpg', $content->extension());
        $this->assertNotSame('', $content->content());
    }

    public function test_image_content_can_be_built_from_an_in_memory_drawing(): void
    {
        $resource = imagecreatetruecolor(10, 10);
        $this->assertNotFalse($resource);

        $drawing = new MemoryDrawing;
        $drawing->setImageResource($resource);
        $drawing->setRenderingFunction(MemoryDrawing::RENDERING_PNG);
        $drawing->setMimeType(MemoryDrawing::MIMETYPE_PNG);

        $content = ImageContent::from($drawing);

        $this->assertSame('png', $content->extension());
        $this->assertStringEndsWith('.png', $content->filename());
        $this->assertStringStartsWith("\x89PNG", $content->content());
    }

    public function test_a_column_reads_the_cell_as_displayed_when_asked_to_format(): void
    {
        $sheet = $this->givenSheet();
        $sheet->getCell('A1')->setValue(0.5);
        $sheet->getCell('A1')->getStyle()->getNumberFormat()->setFormatCode('0.00%');

        // No format of its own, so the column defers to the cell's, which is what
        // WithFormatData asks every column to do.
        $column = Column::make('Rate')->index(1)->formatted();

        $this->assertSame('50.00%', $column->read($sheet->getCell('A1')));
    }

    public function test_type_forces_an_explicit_data_type_when_writing(): void
    {
        $sheet = $this->givenSheet();

        $column = Column::make('Attribute')->index(1)->type(DataType::TYPE_STRING);
        $cell   = $column->write($sheet, 1, ['attribute' => 10]);

        $this->assertSame(DataType::TYPE_STRING, $cell->getDataType());
        $this->assertSame('10', $cell->getValue());
    }

    public function test_format_sets_the_column_number_format(): void
    {
        $sheet = $this->givenSheet();

        $column = Column::make('Attribute')->index(1)->format('0.00%');
        $column->write($sheet, 1, ['attribute' => 0.5]);
        $column->afterWriting($sheet, 1);

        $this->assertSame('0.00%', $sheet->getCell('A1')->getStyle()->getNumberFormat()->getFormatCode());
    }

    public function test_title_returns_the_column_title(): void
    {
        $this->assertSame('Full Name', Column::make('Full Name')->title());
    }

    public function test_text_columns_expose_their_key(): void
    {
        $this->assertSame('full_name', Text::make('Full Name', 'full_name')->getKey());
        $this->assertSame('full_name', Text::make('Full Name')->getKey());
        $this->assertSame('user_name', Text::make('Full Name', 'full_name')->key('user_name')->getKey());

        // Renaming the key must not change which heading the column matches.
        $this->assertSame('full_name', Text::make('Full Name', 'full_name')->key('user_name')->headingKey());
    }

    private function givenSheet(): Worksheet
    {
        return (new Spreadsheet)->getActiveSheet();
    }
}
