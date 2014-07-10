<?php

use Mockery as m;
use Maatwebsite\Excel\Excel;
use Illuminate\Filesystem\Filesystem;

class ExcelTestCase extends PHPUnit_Framework_TestCase {

    /**
     * Mocks
     * @var [type]
     */
    public $phpexcel;
    public $reader;
    public $writer;
    public $excel;
    public $batch;

    /**
     * Setup test case
     */
    public function setUp()
    {
        parent::setUp();

        // Set the mocks
        $this->setMocks();

        // Init our excel class
        $this->excel = new Excel($this->phpexcel, $this->reader, $this->writer);
    }

    /**
     * Test the constructor
     * @return [type] [description]
     */
    public function testConstructor()
    {
        $this->assertInstanceOf('Maatwebsite\Excel\Excel', $this->excel);
    }

    /**
     * Set the mocks
     */
    public function setMocks()
    {
        $this->mockPHPExcel();
        $this->mockReader();
        $this->mockWriter();
        $this->mockBatch();
    }

    /**
     * Mock PHPExcel class
     * @return [type] [description]
     */
    public function mockPHPExcel()
    {
        $this->phpexcel = m::mock('Maatwebsite\Excel\Classes\PHPExcel');
        $this->phpexcel->shouldReceive('getID');
        $this->phpexcel->shouldReceive('disconnectWorksheets');
        $this->phpexcel->shouldReceive('setDefaultProperties');
    }

    /**
     * Mock Reader class
     * @return [type] [description]
     */
    public function mockReader()
    {
        $this->reader = m::mock('Maatwebsite\Excel\Readers\LaravelExcelReader');
        $this->reader->shouldReceive('injectExcel')->with($this->phpexcel);
        $this->reader->shouldReceive('load');
        $this->reader->shouldReceive('setSelectedSheets');
        $this->reader->shouldReceive('setSelectedSheetIndices');
    }

    /**
     * Mock Writer class
     * @return [type] [description]
     */
    public function mockWriter()
    {
        $this->writer = m::mock('Maatwebsite\Excel\Writers\LaravelExcelWriter');
        $this->writer->shouldReceive('injectExcel')->with($this->phpexcel);
        $this->writer->shouldReceive('setTitle');
        $this->writer->shouldReceive('setFileName');
        $this->writer->shouldReceive('shareView')->andReturn($this->writer);
    }

    /**
     * Mock Writer class
     * @return [type] [description]
     */
    public function mockBatch()
    {
        $this->batch = m::mock('Maatwebsite\Excel\Readers\Batch');
        $this->batch->shouldReceive('start')->andReturn('foo');
    }

    /**
     * Teardown
     * @return [type] [description]
     */
    public function tearDown()
    {
        m::close();
    }

}




