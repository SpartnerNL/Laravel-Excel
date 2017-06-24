<?php

namespace Maatwebsite\Excel\Tests\Drivers\PhpSpreadsheet;

use Countable;
use IteratorAggregate;
use Maatwebsite\Excel\Configuration;
use Maatwebsite\Excel\Drivers\PhpSpreadsheet\Loaders\DefaultLoader;
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
    protected static $simpleXlsx = __DIR__.'/../../_data/simple_xlsx.xlsx';

    /**
     * @var Row
     */
    protected static $cachedSheet;

    /**
     * @var Sheet
     */
    protected $sheet;

    public static function setUpBeforeClass()
    {
        static::$cachedSheet = (new Reader(new Configuration(), new DefaultLoader()))->load(static::$simpleXlsx)->sheetByIndex(0);
    }

    public function setUp()
    {
        parent::setUp();

        $this->sheet = clone static::$cachedSheet;
    }

    /**
     * @test
     */
    public function sheet_can_get_sheet_title()
    {
        $this->assertEquals('Simple', $this->sheet->getTitle());
    }

    /**
     * @test
     */
    public function sheet_can_get_sheet_index()
    {
        $this->assertEquals(0, $this->sheet->getSheetIndex());
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
     * @test
     */
    public function sheet_can_convert_itself_to_array()
    {
        $this->assertEquals(
            [
                ['A1', 'B1', 'C1', 'D1'],
                ['A2', 'B2', 'C2', 'D2'],
                ['A3', 'B3', 'C3', 'D3'],
                ['A4', 'B4', 'C4', 'D4'],
                ['A5', 'B5', 'C5', 'D5'],
                ['A6', 'B6', 'C6', 'D6'],
                ['A7', 'B7', 'C7', 'D7'],
                ['A8', 'B8', 'C8', 'D8'],
                ['A9', 'B9', 'C9', 'D9'],
                ['A10', 'B10', 'C10', 'D10'],
                ['A11', 'B11', 'C11', 'D11'],
            ],
            $this->sheet->toArray()
        );
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
