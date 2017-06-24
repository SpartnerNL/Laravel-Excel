<?php

namespace Maatwebsite\Excel\Tests\Drivers\PhpSpreadsheet;

use Maatwebsite\Excel\Configuration;
use Maatwebsite\Excel\Drivers\PhpSpreadsheet\Loaders\DefaultLoader;
use Maatwebsite\Excel\Drivers\PhpSpreadsheet\Reader;
use Maatwebsite\Excel\Drivers\PhpSpreadsheet\Spreadsheet;
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
}
