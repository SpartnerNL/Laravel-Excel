<?php

use Mockery as m;

class ExcelTester extends ExcelTestCase {

    /**
     * Test create
     * @return [type] [description]
     */
    public function testCreate()
    {
        $created = $this->excel->create('test', function() {});
        $this->assertEquals($this->writer, $created);
    }

    /**
     * Test load
     * @return [type] [description]
     */
    public function testLoad()
    {
        $loaded = $this->excel->load('test.csv', function() {});
        $this->assertEquals($this->reader, $loaded);
    }

    /**
     * Test select sheets
     * @return [type] [description]
     */
    public function testSelectSheets()
    {
        $selected = $this->excel->selectSheets(array('sheet'));
        $this->assertEquals($this->excel, $selected);
    }

    /**
     * Test select sheets
     * @return [type] [description]
     */
    public function testSelectSheetsByIndex()
    {
        $selected = $this->excel->selectSheetsByIndex(array('0'));
        $this->assertEquals($this->excel, $selected);
    }

    /**
     * Test the share view
     * @return [type] [description]
     */
    public function testShareView()
    {
        $selected = $this->excel->shareView('filename', array('test'), array('test'));
        $this->assertEquals($this->writer, $selected);
    }

    /**
     * Test load view
     * @return [type] [description]
     */
    public function testLoadView()
    {
        $selected = $this->excel->loadView('filename', array('test'), array('test'));
        $this->assertEquals($this->writer, $selected);
    }

}