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
        $columns = $this->row->cells('B', 'C')->toArray();

        $this->assertEquals(['B1', 'C1'], $columns);
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
