<?php

use Maatwebsite\Excel\Facades\Excel;

class ExcelWriterTest extends TestCase {

    /**
     * Setup
     */
    public function setUp()
    {
        parent::setUp();

        // Set excel class
        $this->excel = App::make('phpexcel');

        // Set writer class
        $this->writer = App::make('excel.writer');
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
        $data = [];
        $mergeData = [];

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
        $sheets = [
            'Worksheet 1 title',
            'Worksheet 2 title',
            'Worksheet 3 title'
        ];

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
            call_user_func_array([$this->writer, $method], [$originalValue]);

            // Get the property back
            $returnedValue = call_user_func_array([$this->writer->getProperties(), $getMethod], []);

            // Check if the properties matches
            $this->assertEquals($originalValue, $returnedValue, $prop . ' doesn\'t match');
        }
    }

    public function testCreateFromArray()
    {
        $info = Excel::create('test', function ($writer)
        {

            $writer->sheet('test', function ($sheet)
            {
                $sheet->fromArray([
                    'test data'
                ]);
            });
        })->store('csv', __DIR__ . '/exports', true);

        $this->assertFileExists($info['full']);
    }

    public function testCreateSheetFromArray()
    {
        $info = Excel::create('test', function ($writer) {
            $writer->sheet('test', function ($sheet) {
                $sheet->createSheetFromArray([
                    'test data'
                ]);
            });
        })->store('csv', __DIR__ . '/exports', true);

        $this->assertFileExists($info['full']);
    }

    public function testCreateSheetFromArrayThrowsException()
    {
        Excel::create('test', function ($writer) {
            $writer->sheet('test', function ($sheet) {
                $this->setExpectedException(PHPExcel_Exception::class);
                $sheet->createSheetFromArray('test data');
            });
        })->store('csv', __DIR__ . '/exports', true);
    }

    public function testNumberPrecision()
    {
        $info = Excel::create('numbers', function ($writer)
        {
            $writer->sheet('test', function ($sheet)
            {
                $sheet->fromArray([
                    ['number' => '1234'],
                    ['number' => '1234.020'],
                    ['number' => '01234HelloWorld'],
                    ['number' => '12345678901234567890'],
                    ['number' => 1234],
                    ['number' => 1234.02],
                    ['number' => 0.0231231234423],
                    ['number' => 4195.99253472222],
                    ['number' => '= A6 + A6'],
                ]);
            });
        })->store('xls', __DIR__ . '/exports', true);

        $this->assertFileExists($info['full']);

        $results = Excel::load($info['full'], null, false, true)->calculate()->toArray();

        $this->assertEquals('1234', $results[0]['number']);
        $this->assertEquals('1234.020', $results[1]['number']);
        $this->assertEquals('01234HelloWorld', $results[2]['number']);
        $this->assertEquals('12345678901234567890', $results[3]['number']);

        $this->assertInternalType('double', $results[4]['number']);
        $this->assertEquals((double) 1234, $results[4]['number']);

        $this->assertInternalType('double', $results[5]['number']);
        $this->assertEquals('1234.02', $results[5]['number']);

        $this->assertInternalType('double', $results[6]['number']);
        $this->assertEquals('0.0231231234423', $results[6]['number']);

        $this->assertInternalType('double', $results[7]['number']);
        $this->assertEquals(4195.99253472222, $results[7]['number']);

        $this->assertEquals(1234 + 1234, $results[8]['number']);
    }

    /**
     * @expectedException Maatwebsite\Excel\Exceptions\LaravelExcelException
     * @expectedExceptionMessage [ERROR] Aborting spreadsheet render: a minimum of 1 sheet is required.
     */
    public function testNoSheets()
    {
        Excel::create('no_sheets', function ($writer) {})->string();
    }

    public function testInvalidExtensionStore()
    {
        $file = Excel::create('numbers', function ($writer)
        {
            $writer->sheet('test', function ($sheet)
            {
                $sheet->fromArray([
                    'number' => 1234
                ]);
            });
        });
        $this->setExpectedException(InvalidArgumentException::class);
        $file->store('invalid file extension');
    }

    public function testInvalidExtensionDownloadExport()
    {
        $file = Excel::create('numbers', function ($writer)
        {
            $writer->sheet('test', function ($sheet)
            {
                $sheet->fromArray([
                    'number' => 1234
                ]);
            });
        });
        $this->setExpectedException(InvalidArgumentException::class);
        $file->download('invalid file extension');
    }

    public function testInvalidExtensionString()
    {
        $file = Excel::create('numbers', function ($writer)
        {
            $writer->sheet('test', function ($sheet)
            {
                $sheet->fromArray([
                    'number' => 1234
                ]);
            });
        });
        $this->setExpectedException(InvalidArgumentException::class);
        $file->string('invalid file extension');
    }

    public function testLoadViewWithDataArray()
    {
        View::addLocation(realpath(__DIR__.'/views'));

        $info = Excel::create('numbers', function ($writer)
        {
            $writer->sheet('test', function ($sheet)
            {
                $sheet->loadView('test')->with(['foo' => 'bar']);
            });
        })->store('csv', __DIR__ . '/exports', true);

        $this->assertFileExists($info['full']);
    }
}
