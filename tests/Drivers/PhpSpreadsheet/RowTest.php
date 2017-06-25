<?php

namespace Maatwebsite\Excel\Tests\Drivers\PhpSpreadsheet;

use Countable;
use IteratorAggregate;
use PHPUnit\Framework\TestCase;
use Maatwebsite\Excel\Configuration;
use Maatwebsite\Excel\Drivers\PhpSpreadsheet\Row;
use Maatwebsite\Excel\Drivers\PhpSpreadsheet\Cell;
use Maatwebsite\Excel\Drivers\PhpSpreadsheet\Reader;
use Maatwebsite\Excel\Tests\Drivers\IterableTestCase;
use Maatwebsite\Excel\Tests\Drivers\CountableTestCase;
use Maatwebsite\Excel\Drivers\PhpSpreadsheet\Loaders\DefaultLoader;

class RowTest extends TestCase
{
    use IterableTestCase, CountableTestCase;

    /**
     * @var string
     */
    protected static $simpleXlsx = __DIR__ . '/../../_data/simple_xlsx.xlsx';

    /**
     * @var Row
     */
    protected static $cachedRow;

    /**
     * @var Row
     */
    protected $row;

    public static function setUpBeforeClass()
    {
        static::$cachedRow = (new Reader(new Configuration(), new DefaultLoader()))
            ->load(static::$simpleXlsx)
            ->sheetByIndex(0)
            ->row(1);
    }

    public function setUp()
    {
        parent::setUp();

        $this->row = clone static::$cachedRow;
    }

    /**
     * @test
     */
    public function row_can_iterate_over_cells()
    {
        // Traversable
        foreach ($this->row as $cell) {
            $this->assertInstanceOf(Cell::class, $cell);
        }

        // Method
        foreach ($this->row->cells() as $cell) {
            $this->assertInstanceOf(Cell::class, $cell);
        }

        // Iterator
        foreach ($this->row->getIterator() as $cell) {
            $this->assertInstanceOf(Cell::class, $cell);
        }
    }

    /**
     * @test
     */
    public function row_can_get_cell_by_column()
    {
        $cell = $this->row->cell('C');

        $this->assertInstanceOf(Cell::class, $cell);
        $this->assertEquals('C1', $cell->getCoordinate());
    }

    /**
     * @test
     */
    public function row_can_get_highest_column()
    {
        $this->assertEquals('D', $this->row->getHighestColumn());
    }

    /**
     * @test
     */
    public function row_can_get_row_number()
    {
        $this->assertEquals(1, $this->row->getRowNumber());
    }

    /**
     * @test
     */
    public function row_can_count_columns()
    {
        $this->assertCount(4, $this->row);
        $this->assertSame(4, count($this->row));
        $this->assertSame(4, $this->row->count());
    }

    /**
     * @test
     */
    public function row_can_convert_itself_to_an_array()
    {
        $columns = $this->row->toArray();

        $this->assertEquals(['A1', 'B1', 'C1', 'D1'], $columns);
    }

    /**
     * @test
     */
    public function row_can_set_start_and_end_column()
    {
        $this->row->setStartColumn('B');
        $this->row->setEndColumn('C');

        $columns = $this->row->toArray();

        $this->assertEquals(['B1', 'C1'], $columns);
    }

    /**
     * @test
     */
    public function row_can_loop_through_cells_with_start_and_end_column()
    {
        $count = 0;
        $start = 'B';
        foreach ($this->row->cells('B', 'C') as $column) {
            $this->assertInstanceOf(Cell::class, $column);
            $this->assertEquals($start . '1', $column->getCoordinate());
            $count++;
            $start++;
        }

        $this->assertEquals(2, $count);
    }

    /**
     * @test
     */
    public function sheet_headings_will_be_empty_when_heading_row_disabled()
    {
        $headings = $this->row->getHeadings();

        $this->assertEquals([], $headings);
    }

    /**
     * @test
     */
    public function can_get_headings_of_sheet()
    {
        $row = (new Reader(new Configuration(), new DefaultLoader()))
            ->load(static::$simpleXlsx)
            ->sheetByIndex(0)
            ->useFirstRowAsHeading()
            ->row(1);

        $headings = $row->getHeadings();

        $this->assertEquals(['A' => 'A1', 'B' => 'B1', 'C' => 'C1', 'D' => 'D1'], $headings);
    }

    /**
     * @test
     */
    public function can_get_cell_by_heading()
    {
        $row = (new Reader(new Configuration(), new DefaultLoader()))
            ->load(static::$simpleXlsx)
            ->sheetByIndex(0)
            ->useRowAsHeading(2)
            ->row(4);

        $this->assertEquals('C4', $row->get('C2'));

        // Non-existing heading returns null
        $this->assertNull($row->get('C98'));
    }

    /**
     * @test
     */
    public function can_check_if_cell_exists_by_heading()
    {
        $row = (new Reader(new Configuration(), new DefaultLoader()))
            ->load(static::$simpleXlsx)
            ->sheetByIndex(0)
            ->useRowAsHeading(2)
            ->row(4);

        $this->assertTrue($row->has('C2'));
        $this->assertFalse($row->has('C89'));
    }

    /**
     * @test
     */
    public function row_has_array_access_by_heading_names()
    {
        $row = (new Reader(new Configuration(), new DefaultLoader()))
            ->load(static::$simpleXlsx)
            ->sheetByIndex(0)
            ->useRowAsHeading(2)
            ->row(4);

        // offset get
        $this->assertEquals('C4', $row['C2']->getValue());

        // offset exists
        $this->assertTrue(isset($row['C2']));
        $this->assertFalse(isset($row['C89']));

        // offset set
        $row['D2'] = 'custom';
        $this->assertEquals('custom', $row->cell('D')->getValue());

        // Unset, removes the value and makes the cell value null
        unset($row['A2']);
        $this->assertNull($row->cell('A')->getValue());
    }

    /**
     * @return IteratorAggregate
     */
    public function getIterable()
    {
        return $this->row;
    }

    /**
     * @return Countable
     */
    public function getCountable()
    {
        return $this->row;
    }
}
