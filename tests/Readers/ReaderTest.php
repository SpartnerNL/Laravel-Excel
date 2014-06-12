<?php

use Mockery as m;
use Maatwebsite\Excel\Readers\LaravelExcelReader;
use Maatwebsite\Excel\Classes;

class ReaderTest extends TestCase {

    /**
     * Setup
     */
    public function setUp()
    {
        parent::setUp();

        // Set excel class
        $this->excel    = App::make('phpexcel');

        // Set writer class
        $this->reader   = App::make('excel.reader');
        $this->reader->injectExcel($this->excel);
    }

    /**
     * Test the excel injection
     * @return [type] [description]
     */
    public function testExcelInjection()
    {
        $this->assertEquals($this->excel, $this->reader->getExcel());
    }

}