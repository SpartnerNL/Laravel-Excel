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
     * Test the excel injection
     * @return [type] [description]
     */
    public function testExcelInjection()
    {
        $this->assertEquals($this->excel, $this->writer->getExcel());
    }

    /**
     * Test setTitle()
     * @return [type] [description]
     */
    public function testSetTitle()
    {
        $title = 'Workbook Title';
        $titleSet = $this->writer->setTitle($title);
        $this->assertEquals($this->writer, $titleSet);

        // Test if title was really set
        $this->assertEquals($this->writer->getTitle(),                  $title);
        $this->assertEquals($this->writer->getProperties()->getTitle(), $title);
    }

    /**
     * Test setTitle()
     * @return [type] [description]
     */
    public function testSetFilename()
    {
        $filename       = 'filename';
        $filenameSet    = $this->writer->setFileName($filename);
        $this->assertEquals($this->writer, $filenameSet);

        // Test if title was really set
        $this->assertEquals($this->writer->getFileName(), $filename);
    }


    /**
     * Test the share view
     * @return [type] [description]
     */
    public function testShareView()
    {
        // Set params
        $view = 'excel';
        $data = array();
        $mergeData = array();

        $viewShared = $this->writer->shareView($view, $data, $mergeData);
        $this->assertEquals($this->writer, $viewShared);

        // Get the parser
        $parser = $this->writer->getParser();

        // Test if parse data was set
        $this->assertEquals($parser->getView(),         $view);
        $this->assertEquals($parser->getData(),         $data);
        $this->assertEquals($parser->getMergeData(),    $mergeData);
    }

    /**
     * Test basic sheet creation
     * @return [type] [description]
     */
    public function testSheet()
    {
        $title = 'Worksheet Title';
        $sheetCreated = $this->writer->sheet($title);

        $this->assertEquals($this->writer, $sheetCreated);

        // Test if title was really set
        $this->assertEquals($this->writer->getSheet()->getTitle(), $title);
    }

    /**
     * Test sheet closure
     * @return [type] [description]
     */
    public function testSheetClosure()
    {
        $title = 'Worksheet Title';
        $closureTitle = 'Closure Title';

        $this->writer->sheet($title, function($sheet) use($closureTitle) {
            $sheet->setTitle($closureTitle);
        });

        // Test if title was really set
        $this->assertEquals($this->writer->getSheet()->getTitle(), $closureTitle);
    }

    /**
     * Test multiple sheet creation
     * @return [type] [description]
     */
    public function testMultipleSheets()
    {
        // Set sheet titles
        $sheets = array(
            'Worksheet 1 title',
            'Worksheet 2 title',
            'Worksheet 3 title'
        );

        // Create the sheets
        foreach($sheets as $sheetTitle)
        {
            $this->writer->sheet($sheetTitle);
        }

        // Count amount of sheets
        $this->assertEquals(count($sheets), $this->writer->getSheetCount());

        // Test if all sheet titles where set correctly
        foreach($sheets as $sheetTitle)
        {
            $this->assertContains($sheetTitle, $this->writer->getSheetNames());
        }
    }

    /**
     * Test setting properties (creator, ...)
     * @return [type] [description]
     */
    public function testSetProperties()
    {
        // Get available properties
        $properties = $this->excel->getAllowedProperties();

        // Loop through them
        foreach($properties as $prop)
        {
            // Set a random value
            $originalValue = rand();

            // Set needed set/get methods
            $method     = 'set' . ucfirst($prop);
            $getMethod  = 'get' . ucfirst($prop);

            // Set the property with the random value
            call_user_func_array(array($this->writer, $method), array($originalValue));

            // Get the property back
            $returnedValue = call_user_func_array(array($this->writer->getProperties(), $getMethod), array());

            // Check if the properties matches
            $this->assertEquals($originalValue, $returnedValue, $prop . ' doesn\'t match');
        }
    }

}