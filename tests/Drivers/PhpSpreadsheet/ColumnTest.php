<?php

namespace Maatwebsite\Excel\Tests\Drivers\PhpSpreadsheet;

use IteratorAggregate;
use PHPUnit\Framework\TestCase;
use Maatwebsite\Excel\Configuration;
use Maatwebsite\Excel\Drivers\PhpSpreadsheet\Column;
use Maatwebsite\Excel\Drivers\PhpSpreadsheet\Cell;
use Maatwebsite\Excel\Drivers\PhpSpreadsheet\Reader;
use Maatwebsite\Excel\Tests\Drivers\IterableTestCase;
use Maatwebsite\Excel\Drivers\PhpSpreadsheet\Loaders\DefaultLoader;

class ColumnTest extends TestCase
{
    use IterableTestCase;

    /**
     * @var string
     */
    protected static $simpleXlsx = __DIR__ . '/../../_data/simple_xlsx.xlsx';

    /**
     * @var Column
     */
    protected static $cachedColumn;

    /**
     * @var Column
     */
    protected $column;

    public static function setUpBeforeClass()
    {
        static::$cachedColumn = (new Reader(new Configuration(), new DefaultLoader()))
            ->load(static::$simpleXlsx)
            ->sheetByIndex(0)
            ->column('A');
    }

    public function setUp()
    {
        parent::setUp();

        $this->column = clone static::$cachedColumn;
    }

    /**
     * @test
     */
    public function column_can_iterate_over_cells()
    {
        // Traversable
        foreach ($this->column as $cell) {
            $this->assertInstanceOf(Cell::class, $cell);
        }

        // Method
        foreach ($this->column->cells() as $cell) {
            $this->assertInstanceOf(Cell::class, $cell);
        }

        // Iterator
        foreach ($this->column->getIterator() as $cell) {
            $this->assertInstanceOf(Cell::class, $cell);
        }

        // Iterator
        foreach ($this->column->getCellIterator() as $cell) {
            $this->assertInstanceOf(Cell::class, $cell);
        }
    }

    /**
     * @test
     */
    public function can_set_start_end_row()
    {
        $this->column->setStartRow(4);
        $this->column->setEndRow(9);

        $count = 0;
        $start = 4;
        foreach ($this->column as $cell) {
            $this->assertInstanceOf(Cell::class, $cell);
            $this->assertEquals('A' . $start, $cell->getCoordinate());
            $count++;
            $start++;
        }

        $this->assertEquals(6, $count);
    }

    public function column_can_iterator_with_start_end_row()
    {
        $count = 0;
        $start = 3;
        foreach ($this->column->cells(3, 7) as $cell) {
            $this->assertInstanceOf(Cell::class, $cell);
            $this->assertEquals('A' . $start, $cell->getCoordinate());
            $count++;
            $start++;
        }

        $this->assertEquals(5, $count);
    }

    /**
     * @test
     */
    public function column_can_get_cell_by_row()
    {
        $cell = $this->column->cell(3);

        $this->assertInstanceOf(Cell::class, $cell);
        $this->assertEquals('A3', $cell->getCoordinate());
    }

    /**
     * @test
     */
    public function column_can_convert_itself_to_array()
    {
        $this->assertEquals(
            ['A1', 'A2', 'A3', 'A4', 'A5', 'A6', 'A7', 'A8', 'A9', 'A10', 'A11'],
            $this->column->toArray()
        );
    }

    /**
     * @return IteratorAggregate
     */
    public function getIterable()
    {
        return $this->column;
    }
}
