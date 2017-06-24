<?php

namespace Maatwebsite\Excel\Tests\Drivers\PhpSpreadsheet;

use Maatwebsite\Excel\Drivers\PhpSpreadsheet\Reader;
use Maatwebsite\Excel\Drivers\PhpSpreadsheet\Sheet;
use PHPUnit\Framework\TestCase;

class ReaderTest extends TestCase
{
    /**
     * @var string
     */
    protected $simpleXlsx = __DIR__ . '/../../_data/simple_xlsx.xlsx';

    /**
     * @var Reader
     */
    protected $reader;

    public function setUp()
    {
        parent::setUp();

        $this->reader = new Reader();
    }

    /**
     * @test
     */
    public function reader_can_load_sheet_by_index_or_name()
    {
        $reader = $this->reader->load($this->simpleXlsx);

        $sheet = $reader->sheet(0);
        $this->assertInstanceOf(Sheet::class, $sheet);

        $sheet = $reader->sheet('Simple');
        $this->assertInstanceOf(Sheet::class, $sheet);
    }

    /**
     * @test
     */
    public function reader_can_load_sheet_by_index()
    {
        $reader = $this->reader->load($this->simpleXlsx);
        $sheet  = $reader->sheetByIndex(0);

        $this->assertInstanceOf(Sheet::class, $sheet);
    }

    /**
     * @test
     */
    public function reader_can_load_sheet_by_name()
    {
        $reader = $this->reader->load($this->simpleXlsx);
        $sheet  = $reader->sheetByName('Simple');

        $this->assertInstanceOf(Sheet::class, $sheet);
    }

    /**
     * @test
     * @expectedException \Maatwebsite\Excel\Exceptions\SheetNotFoundException
     * @expectedExceptionMessage Your requested sheet index: 999 is out of bounds. The actual number of sheets is 1.
     */
    public function reader_will_throw_exception_if_sheet_index_not_exists()
    {
        $reader = $this->reader->load($this->simpleXlsx);
        $reader->sheetByIndex(999);
    }

    /**
     * @test
     * @expectedException \Maatwebsite\Excel\Exceptions\SheetNotFoundException
     * @expectedExceptionMessage Sheet with name [non-existing] could not be found
     */
    public function reader_will_throw_exception_if_sheet_name_not_exists()
    {
        $reader = $this->reader->load($this->simpleXlsx);
        $reader->sheetByName('non-existing');
    }

    /**
     * @test
     */
    public function test_can_count_the_sheets()
    {
        $reader = $this->reader->load($this->simpleXlsx);

        $this->assertEquals(1, count($reader));
        $this->assertEquals(1, $reader->count());
    }

    /**
     * @test
     */
    public function reader_can_iterator_over_sheets()
    {
        $reader = $this->reader->load($this->simpleXlsx);

        foreach ($reader as $sheet) {
            $this->assertInstanceOf(Sheet::class, $sheet);
        }

        foreach ($reader->sheets() as $sheet) {
            $this->assertInstanceOf(Sheet::class, $sheet);
        }

        foreach ($reader->getIterator() as $sheet) {
            $this->assertInstanceOf(Sheet::class, $sheet);
        }
    }
}
