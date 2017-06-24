<?php

namespace Maatwebsite\Excel\Tests\Drivers\PhpSpreadsheet;

use Countable;
use IteratorAggregate;
use Maatwebsite\Excel\Drivers\PhpSpreadsheet\Cell;
use Maatwebsite\Excel\Drivers\PhpSpreadsheet\Reader;
use Maatwebsite\Excel\Drivers\PhpSpreadsheet\Row;
use Maatwebsite\Excel\Tests\Drivers\CountableTestCase;
use Maatwebsite\Excel\Tests\Drivers\IterableTestCase;
use PHPUnit\Framework\TestCase;

class RowTest extends TestCase
{
    use IterableTestCase, CountableTestCase;

    /**
     * @var string
     */
    protected $simpleXlsx = __DIR__.'/../../_data/simple_xlsx.xlsx';

    /**
     * @var Row
     */
    protected $row;

    public function setUp()
    {
        parent::setUp();

        $this->row = (new Reader())
            ->load($this->simpleXlsx)
            ->sheetByIndex(0)
            ->row(1);
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
