<?php


class ExcelTester extends ExcelTestCase
{
    /**
     * Test select sheets.
     * @return
     */
    public function testSelectSheets()
    {
        $selected = $this->excel->selectSheets(['sheet']);
        $this->assertEquals($this->excel, $selected);
    }

    /**
     * Test select sheets.
     * @return
     */
    public function testSelectSheetsByIndex()
    {
        $selected = $this->excel->selectSheetsByIndex(['0']);
        $this->assertEquals($this->excel, $selected);
    }

    /**
     * Test the share view.
     * @return
     */
    public function testShareView()
    {
        $selected = $this->excel->shareView('filename', ['test'], ['test']);
        $this->assertEquals($this->writer, $selected);
    }

    /**
     * Test load view.
     * @return
     */
    public function testLoadView()
    {
        $selected = $this->excel->loadView('filename', ['test'], ['test']);
        $this->assertEquals($this->writer, $selected);
    }
}
