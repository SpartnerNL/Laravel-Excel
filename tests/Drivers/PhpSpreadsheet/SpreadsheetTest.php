<?php

namespace Maatwebsite\Excel\Tests\Drivers\PhpSpreadsheet;

use Countable;
use IteratorAggregate;
use PHPUnit\Framework\TestCase;
use Maatwebsite\Excel\Configuration;
use Maatwebsite\Excel\Drivers\PhpSpreadsheet\Sheet;
use Maatwebsite\Excel\Drivers\PhpSpreadsheet\Reader;
use Maatwebsite\Excel\Tests\Drivers\IterableTestCase;
use Maatwebsite\Excel\Tests\Drivers\CountableTestCase;
use Maatwebsite\Excel\Drivers\PhpSpreadsheet\Spreadsheet;
use Maatwebsite\Excel\Drivers\PhpSpreadsheet\Loaders\DefaultLoader;

class SpreadsheetTest extends TestCase
{
    use IterableTestCase, CountableTestCase;

    /**
     * @var string
     */
    protected static $simpleXlsx = __DIR__ . '/../../_data/simple_xlsx.xlsx';

    /**
     * @var Spreadsheet
     */
    protected static $cachedSpreadsheet;

    /**
     * @var Spreadsheet
     */
    protected $spreadsheet;

    public static function setUpBeforeClass()
    {
        static::$cachedSpreadsheet = (new Reader(new Configuration(), new DefaultLoader()))->load(static::$simpleXlsx);
    }

    public function setUp()
    {
        parent::setUp();

        $this->spreadsheet = clone static::$cachedSpreadsheet;
    }

    /**
     * @test
     */
    public function spreadsheet_can_get_title()
    {
        // TODO: add actual title to the spreadsheet
        $this->assertEquals('', $this->spreadsheet->getTitle());
    }

    /**
     * @test
     */
    public function reader_can_load_sheet_by_index_or_name()
    {
        $sheet = $this->spreadsheet->sheet(0, function ($sheet) {
            $this->assertInstanceOf(Sheet::class, $sheet);
        });

        $this->assertInstanceOf(Sheet::class, $sheet);

        $sheet = $this->spreadsheet->sheet('Simple', function ($sheet) {
            $this->assertInstanceOf(Sheet::class, $sheet);
        });

        $this->assertInstanceOf(Sheet::class, $sheet);
    }

    /**
     * @test
     */
    public function reader_can_load_sheet_by_index()
    {
        $sheet = $this->spreadsheet->sheetByIndex(0);

        $this->assertInstanceOf(Sheet::class, $sheet);
    }

    /**
     * @test
     */
    public function reader_can_load_sheet_by_name()
    {
        $sheet = $this->spreadsheet->sheetByName('Simple');

        $this->assertInstanceOf(Sheet::class, $sheet);
    }

    /**
     * @test
     * @expectedException \Maatwebsite\Excel\Exceptions\SheetNotFoundException
     * @expectedExceptionMessage Your requested sheet index: 999 is out of bounds. The actual number of sheets is 1.
     */
    public function reader_will_throw_exception_if_sheet_index_not_exists()
    {
        $this->spreadsheet->sheetByIndex(999);
    }

    /**
     * @test
     * @expectedException \Maatwebsite\Excel\Exceptions\SheetNotFoundException
     * @expectedExceptionMessage Sheet with name [non-existing] could not be found
     */
    public function reader_will_throw_exception_if_sheet_name_not_exists()
    {
        $this->spreadsheet->sheetByName('non-existing');
    }

    /**
     * @test
     */
    public function reader_can_get_first_sheet()
    {
        $sheet = $this->spreadsheet->first();

        $this->assertInstanceOf(Sheet::class, $sheet);
        $this->assertSame(0, $sheet->getSheetIndex());
    }

    /**
     * @test
     */
    public function can_count_the_sheets()
    {
        $this->assertEquals(1, count($this->spreadsheet));
        $this->assertEquals(1, $this->spreadsheet->count());
    }

    /**
     * @test
     */
    public function reader_can_iterate_over_sheets()
    {
        // Traversable
        foreach ($this->spreadsheet as $sheet) {
            $this->assertInstanceOf(Sheet::class, $sheet);
        }

        // Method
        foreach ($this->spreadsheet->sheets() as $sheet) {
            $this->assertInstanceOf(Sheet::class, $sheet);
        }

        // Iterator
        foreach ($this->spreadsheet->getIterator() as $sheet) {
            $this->assertInstanceOf(Sheet::class, $sheet);
        }
    }

    /**
     * @return IteratorAggregate
     */
    public function getIterable()
    {
        return $this->spreadsheet;
    }

    /**
     * @return Countable
     */
    public function getCountable()
    {
        return $this->spreadsheet;
    }
}
