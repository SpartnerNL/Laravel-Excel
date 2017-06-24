<?php

namespace Maatwebsite\Excel\Tests\Drivers\PhpSpreadsheet;

use Maatwebsite\Excel\Sheet;
use PHPUnit\Framework\TestCase;
use Maatwebsite\Excel\Configuration;
use Maatwebsite\Excel\Drivers\PhpSpreadsheet\Reader;
use Maatwebsite\Excel\Drivers\PhpSpreadsheet\Spreadsheet;
use Maatwebsite\Excel\Drivers\PhpSpreadsheet\Loaders\DefaultLoader;

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

    /**
     * @var Spreadsheet
     */
    protected $spreadsheet;

    public function setUp()
    {
        parent::setUp();

        $this->reader = new Reader(new Configuration(), new DefaultLoader());
    }

    /**
     * @test
     */
    public function reader_can_load_spreadsheet()
    {
        $spreadsheet = $this->reader->load($this->simpleXlsx, function ($spreadsheet) {
            $this->assertInstanceOf(Spreadsheet::class, $spreadsheet);
        });

        $this->assertInstanceOf(Spreadsheet::class, $spreadsheet);
    }

    /**
     * @test
     */
    public function reader_can_load_with_custom_loader()
    {
        $reader = new Reader(new Configuration(), function () {
            $spreadsheet = new \PhpOffice\PhpSpreadsheet\Spreadsheet();

            $spreadsheet->getProperties()->setTitle('Custom-loaded spreadsheet');

            return $spreadsheet;
        });

        $spreadsheet = $reader->load($this->simpleXlsx);

        $this->assertInstanceOf(Spreadsheet::class, $spreadsheet);
        $this->assertEquals('Custom-loaded spreadsheet', $spreadsheet->getTitle());
    }

    /**
     * @test
     * @expectedException \TypeError
     * @expectedExceptionMessage  Argument 1 passed to Maatwebsite\Excel\Drivers\PhpSpreadsheet\Reader::setLoader()
     *                            must be callable or null, string given
     */
    public function reader_will_throw_exception_if_custom_loader_is_not_callable()
    {
        $reader = new Reader(new Configuration(), new DefaultLoader());
        $reader->setLoader('test');

        $spreadsheet = $reader->load($this->simpleXlsx);

        $this->assertInstanceOf(Spreadsheet::class, $spreadsheet);
        $this->assertEquals('Custom-loaded spreadsheet', $spreadsheet->getTitle());
    }

    /**
     * @test
     */
    public function reader_can_load_and_parse_simple_xlsx()
    {
        $this->reader->load($this->simpleXlsx, function (Spreadsheet $spreadsheet) {
            $spreadsheet->sheet('Simple', function (Sheet $sheet) {
                $this->assertEquals('Simple', $sheet->getTitle());

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
                    $sheet->toArray()
                );

                $this->assertEquals('B8', $sheet->cell('B8')->getValue());
                $this->assertEquals(8, $sheet->row(8)->getRowNumber());
                $this->assertEquals('C', $sheet->column('C')->getColumnIndex());
            });
        });
    }
}
