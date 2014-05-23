<?php

use Mockery as m;
use Maatwebsite\Excel\Writers\LaravelExcelWriter;
use Maatwebsite\Excel\Classes;

class ExcelWriterTest extends TestCase {

    /**
     * Setup
     */
    public function setUp()
    {
        parent::setUp();

        // Set excel class
        $this->excel    = App::make('phpexcel');

        // Set writer class
        $this->writer   = App::make('excel.writer');
        $this->writer->injectExcel($this->excel);

    }

    /**
     * Test setCreator()
     * @return [type] [description]
     */
    public function testSetCreator()
    {
        $creatorSet = $this->writer->setCreator('Maatwebsite');
        $this->assertEquals($this->writer, $creatorSet);
    }

    /**
     * Test setTitle()
     * @return [type] [description]
     */
    public function testSetTitle()
    {
        $titleSet = $this->writer->setTitle('Workbook Title');
        $this->assertEquals($this->writer, $titleSet);
    }

}