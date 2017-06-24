<?php

namespace Maatwebsite\Excel\Tests\Drivers\PhpSpreadsheet;

use Countable;
use IteratorAggregate;
use Maatwebsite\Excel\Drivers\PhpSpreadsheet\Reader;
use Maatwebsite\Excel\Drivers\PhpSpreadsheet\Row;
use Maatwebsite\Excel\Drivers\PhpSpreadsheet\Sheet;
use Maatwebsite\Excel\Tests\Drivers\CountableTestCase;
use Maatwebsite\Excel\Tests\Drivers\IterableTestCase;
use PHPUnit\Framework\TestCase;

class SheetTest extends TestCase
{
    use IterableTestCase, CountableTestCase;

    /**
     * @var string
     */
    protected $simpleXlsx = __DIR__.'/../../_data/simple_xlsx.xlsx';

    /**
     * @var Sheet
     */
    protected $sheet;

    public function setUp()
    {
        parent::setUp();

        $this->sheet = (new Reader())->load($this->simpleXlsx)->sheetByIndex(0);
    }

    /**
     * @test
     */
    public function sheet_can_iterate_over_rows()
    {
        // Traversable
        foreach ($this->sheet as $row) {
            $this->assertInstanceOf(Row::class, $row);
        }

        // Method
        foreach ($this->sheet->rows() as $row) {
            $this->assertInstanceOf(Row::class, $row);
        }

        // Iterator
        foreach ($this->sheet->getIterator() as $row) {
            $this->assertInstanceOf(Row::class, $row);
        }
    }

    /**
     * @test
     */
    public function sheet_can_set_start_and_end_row()
    {
        $this->sheet->setStartRow(2);
        $this->sheet->setEndRow(5);

        $count = 0;
        foreach ($this->sheet->rows() as $row) {
            $count++;
            $this->assertInstanceOf(Row::class, $row);
        }

        $this->assertEquals(4, $count);
    }

    /**
     * @test
     */
    public function sheet_can_iterator_starting_on_a_certain_row()
    {
        $count = 0;
        foreach ($this->sheet->rows(2, 5) as $row) {
            $count++;
            $this->assertInstanceOf(Row::class, $row);
        }

        $this->assertEquals(4, $count);
    }

    /**
     * @test
     */
    public function sheet_can_count_rows()
    {
        $this->assertCount(11, $this->sheet);
        $this->assertSame(11, count($this->sheet));
        $this->assertSame(11, $this->sheet->count());
    }

    /**
     * @test
     */
    public function sheet_can_get_row_by_row_number()
    {
        $row = $this->sheet->row(10);

        $this->assertInstanceOf(Row::class, $row);
        $this->assertEquals(10, $row->getRowNumber());
    }

    /**
     * @test
     */
    public function sheet_can_get_first_row()
    {
        $row = $this->sheet->first();

        $this->assertInstanceOf(Row::class, $row);
        $this->assertEquals(1, $row->getRowNumber());
    }

    /**
     * @return IteratorAggregate
     */
    public function getIterable()
    {
        return $this->sheet;
    }

    /**
     * @return Countable
     */
    public function getCountable()
    {
        return $this->sheet;
    }
}
