<?php

use Mockery as m;
use Maatwebsite\Excel\Readers\LaravelExcelReader;
use Maatwebsite\Excel\Classes;

class ChineseXlsReaderTest extends TestCase {

    /**
     * Test csv file
     * @var [type]
     */
    protected $xls;

    /**
     * Loaded csv file
     * @var [type]
     */
    protected $loadedXls;

    /**
     * Setup
     */
    public function setUp()
    {
        parent::setUp();

        // Disable to ascii
        Config::set('excel.import.to_ascii', false);

        // Set excel class
        $this->excel    = App::make('phpexcel');

        // Set writer class
        $this->reader   = App::make('excel.reader');
        $this->reader->injectExcel($this->excel);

        // Load csv file
        $this->loadChineseXls();
    }

    /**
     * Test loading a csv file
     * @return [type] [description]
     */
    public function testloadChineseXls()
    {
        $this->assertEquals($this->reader, $this->loadedXls);
        $this->assertInstanceOf('PHPExcel', $this->reader->getExcel());
    }

    /**
     * Test get
     * @return [type] [description]
     */
    public function testGet()
    {
        $got = $this->loadedXls->get();
        $this->assertInstanceOf('Maatwebsite\Excel\Collections\RowCollection', $got);
        $this->assertCount(2, $got);
    }


    /**
     * Test toArray
     * @return [type] [description]
     */
    public function testToArray()
    {
        $array = $this->loadedXls->toArray();
        $this->assertEquals(array(

            array(
                '商品編號'  => 'L01A01SY047',
                '商品名稱'  => 'LED T8燈管',
                '實際數量'  => 1,
            ),
            array(
                '商品編號'  => 'L01A01SY046',
                '商品名稱'  => 'LED T8燈管',
                '實際數量'  => 1,
            )

        ), $array);
    }

    /**
     * Load a csv file
     * @return [type] [description]
     */
    protected function loadChineseXls()
    {
        // Set test csv file
        $this->xls = __DIR__ . '/files/' . 'chinese.xls';

        // Loaded csv
        $this->loadedXls = $this->reader->load($this->xls);
    }

}