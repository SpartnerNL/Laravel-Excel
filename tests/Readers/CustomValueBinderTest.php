<?php

class CustomValuBinderTest extends TestCase {

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
        $this->reader->noHeading(true);

        // Set value binder
        $binder = new StubValueBinder();
        $this->reader->setValueBinder($binder);

        // Load csv file
        $this->loadFile();
    }

    /**
     * Tear down
     */
    public function tearDown()
    {
        // Necessary to reset the value binder back to default so that future test classes are unaffected.
        $this->reader->resetValueBinder();
    }

    public function testDefaultGet()
    {
        $got = $this->loadedFile->get();
        $this->assertInstanceOf('Maatwebsite\Excel\Collections\RowCollection', $got);
        $this->assertCount(5, $got);
    }

    public function testNumeric()
    {
        $got = $this->loadedFile->toArray();

        $this->assertTrue(is_string($got[0][0]));
        $this->assertEquals('00123', $got[0][0]);
    }

    public function testRealNull()
    {
        $got = $this->loadedFile->toArray();

        $this->assertTrue(is_null($got[1][0]));
        $this->assertEquals('', $got[1][0]);
    }

    public function testStringNull()
    {
        $got = $this->loadedFile->toArray();

        $this->assertTrue(is_string($got[2][0]));
        $this->assertEquals('null', $got[2][0]);
    }

    public function testEquation()
    {
        $got = $this->loadedFile->toArray();

        $this->assertTrue(is_string($got[3][0]));
        $this->assertEquals('=1+2', $got[3][0]);
    }

    public function testBoolean()
    {
        $got = $this->loadedFile->toArray();

        $this->assertTrue(is_string($got[4][0]));
        $this->assertEquals('true', $got[4][0]);
    }

    /**
     * Load a csv file
     * @return [type] [description]
     */
    protected function loadFile()
    {
        // Set test csv file
        $file = __DIR__ . '/files/' . 'customBinder.csv';

        // Loaded csv
        $this->loadedFile = $this->reader->load($file);
    }
}

class StubValueBinder extends PHPExcel_Cell_DefaultValueBinder implements PHPExcel_Cell_IValueBinder
{
    public function bindValue(PHPExcel_Cell $cell, $value = null)
    {
        $cell->setValueExplicit($value, PHPExcel_Cell_DataType::TYPE_STRING);

        return true;
    }
}