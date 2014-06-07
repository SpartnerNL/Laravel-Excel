<?php

use Mockery as m;
use Maatwebsite\Excel\Readers\LaravelExcelReader;
use Maatwebsite\Excel\Classes;

class CsvReaderTest extends TestCase {

    /**
     * Test csv file
     * @var [type]
     */
    protected $csvFile;

    /**
     * Loaded csv file
     * @var [type]
     */
    protected $loadedCsv;

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

        // Load csv file
        $this->loadCsv();
    }

    /**
     * Test loading a csv file
     * @return [type] [description]
     */
    public function testLoadCsv()
    {
        $this->assertEquals($this->reader, $this->loadedCsv);
        $this->assertInstanceOf('PHPExcel', $this->reader->getExcel());
    }

    /**
     * Test get
     * @return [type] [description]
     */
    public function testGet()
    {
        $got = $this->loadedCsv->get();
        $this->assertInstanceOf('Maatwebsite\Excel\Collections\RowCollection', $got);
        $this->assertCount(5, $got);
    }

    /**
     * Test get with columns
     * @return [type] [description]
     */
    public function testGetWithColumns()
    {
        $columns = array('heading1', 'heading2');
        $got = $this->loadedCsv->get($columns);

        $this->assertInstanceOf('Maatwebsite\Excel\Collections\RowCollection', $got);
        $this->assertCount(5, $got);
    }

    /**
     * Test all
     * @return [type] [description]
     */
    public function testAll()
    {
        $all = $this->loadedCsv->all();
        $this->assertInstanceOf('Maatwebsite\Excel\Collections\RowCollection', $all);
        $this->assertCount(5, $all);
    }

    /**
     * Test first
     * @return [type] [description]
     */
    public function testFirst()
    {
        $first = $this->loadedCsv->first();
        $this->assertInstanceOf('Maatwebsite\Excel\Collections\CellCollection', $first);

        // 3 columns
        $this->assertCount(3, $first);
    }

    /**
     * Test first with columns
     * @return [type] [description]
     */
    public function testFirstWithColumns()
    {
        $columns = array('heading1', 'heading2');
        $first = $this->loadedCsv->first($columns);

        $this->assertInstanceOf('Maatwebsite\Excel\Collections\CellCollection', $first);
        $this->assertCount(count($columns), $first);
    }

    /**
     * Test each
     * @return [type] [description]
     */
    public function testEach()
    {
        $me = $this;

        $this->loadedCsv->each(function($cells) use($me) {

            $me->assertInstanceOf('Maatwebsite\Excel\Collections\CellCollection', $cells);

        });
    }

    /**
     * Test toArray
     * @return [type] [description]
     */
    public function testToArray()
    {
        $array = $this->loadedCsv->toArray();
        $this->assertEquals(array(

            array(
                'heading1'  => 'test',
                'heading2'  => 'test',
                'heading3'  => 'test',
            ),
            array(
                'heading1'  => 'test',
                'heading2'  => 'test',
                'heading3'  => 'test',
            ),
            array(
                'heading1'  => 'test',
                'heading2'  => 'test',
                'heading3'  => 'test',
            ),
            array(
                'heading1'  => 'test',
                'heading2'  => 'test',
                'heading3'  => 'test',
            ),
            array(
                'heading1'  => 'test',
                'heading2'  => 'test',
                'heading3'  => 'test',
            )

        ), $array);
    }

    /**
     * Test remember method
     * @return [type] [description]
     */
    public function testRemember()
    {
        $remembered = $this->loadedCsv->remember(10);

        $this->assertEquals($this->reader, $remembered);
        $this->assertEquals(10, $remembered->cacheMinutes);
        $this->assertTrue($remembered->remembered);
    }

    /**
     * Test set selected sheets
     * @return [type] [description]
     */
    public function testByConfig()
    {
        $config = $this->loadedCsv->byConfig('excel::import.sheets');
        $this->assertInstanceOf('Maatwebsite\Excel\Collections\SheetCollection', $config);
    }

    /**
     * Test set selected sheets
     * @return [type] [description]
     */
    public function testByConfigCallback()
    {
        $me = $this;

        $config = $this->loadedCsv->byConfig('excel::import.sheets', function($config) use($me)
        {
            $me->assertInstanceOf('Maatwebsite\Excel\Readers\ConfigReader', $config);
        });

        $this->assertInstanceOf('Maatwebsite\Excel\Collections\SheetCollection', $config);
    }

    /**
     * Test take
     * @return [type] [description]
     */
    public function testTake()
    {
        $taken = $this->loadedCsv->take(2);
        $this->assertEquals(2, $taken->getLimit());
        $this->assertCount(2, $taken->get());
    }

    /**
     * Test limit
     * @return [type] [description]
     */
    public function testSkip()
    {
        $taken = $this->loadedCsv->skip(1);
        $this->assertEquals(1, $taken->getSkip());
        $this->assertCount(4, $taken->get());
    }

    /**
     * Test limit
     * @return [type] [description]
     */
    public function testLimit()
    {
        $taken = $this->loadedCsv->limit(2, 1);
        $this->assertEquals(2, $taken->getLimit());
        $this->assertEquals(1, $taken->getSkip());
        $this->assertCount(2, $taken->get());
    }

    /**
     * Test select columns
     * @return [type] [description]
     */
    public function testSelect()
    {
        $columns = array('heading1', 'heading2');

        $taken = $this->loadedCsv->select($columns);
        $this->assertEquals($columns, $taken->columns);
    }

    /**
     * Test set date format
     * @return [type] [description]
     */
    public function testSetDateFormat()
    {
        $set = $this->loadedCsv->setDateFormat('Y-m-d');
        $this->assertEquals('Y-m-d', $set->getDateFormat());
    }

    public function testFormatDates()
    {
        $set = $this->loadedCsv->formatDates(true, 'Y-m-d');
        $this->assertTrue($set->needsDateFormatting());
        $this->assertEquals('Y-m-d', $set->getDateFormat());
    }

    public function testSetDateColumns()
    {
        $set = $this->loadedCsv->setDateColumns('created_at', 'deleted_at');
        $this->assertTrue($set->needsDateFormatting());
        $this->assertEquals(array('created_at', 'deleted_at'), $set->getDateColumns());
    }

    public function testSetSeparator()
    {
        $set = $this->loadedCsv->setSeperator('-');
        $this->assertEquals('-', $set->getSeperator());
    }

    public function testSetDelimiter()
    {
        $set = $this->loadedCsv->setDelimiter(';');
        $this->assertEquals(';', $set->getDelimiter());
    }

    public function testCalculate()
    {
        $set = $this->loadedCsv->calculate();
        $this->assertTrue($set->needsCalculation());
    }

    public function testIgnoreEmpty()
    {
        $set = $this->loadedCsv->ignoreEmpty();
        $this->assertTrue($set->needsIgnoreEmpty());
    }

    /**
     * Load a csv file
     * @return [type] [description]
     */
    protected function loadCsv()
    {
        // Set test csv file
        $this->csvFile = __DIR__ . '/' . 'test.csv';

        // Loaded csv
        $this->loadedCsv = $this->reader->load($this->csvFile);
    }

}