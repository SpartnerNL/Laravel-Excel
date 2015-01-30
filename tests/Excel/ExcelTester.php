<?php

use Mockery as m;

class ExcelTester extends ExcelTestCase {

    /**
     * Test select sheets
     * @return
     */
    public function testSelectSheets()
    {
        $selected = $this->excel->selectSheets(array('sheet'));
        $this->assertEquals($this->excel, $selected);
    }

    /**
     * Test select sheets
     * @return
     */
    public function testSelectSheetsByIndex()
    {
        $selected = $this->excel->selectSheetsByIndex(array('0'));
        $this->assertEquals($this->excel, $selected);
    }

    /**
     * Test the share view
     * @return
     */
    public function testShareView()
    {
        $selected = $this->excel->shareView('filename', array('test'), array('test'));
        $this->assertEquals($this->writer, $selected);
    }

    /**
     * Test load view
     * @return
     */
    public function testLoadView()
    {
        $selected = $this->excel->loadView('filename', array('test'), array('test'));
        $this->assertEquals($this->writer, $selected);
    }
}