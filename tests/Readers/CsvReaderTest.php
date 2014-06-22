<?php

require_once('traits/ImportTrait.php');

use Mockery as m;
use Maatwebsite\Excel\Readers\LaravelExcelReader;
use Maatwebsite\Excel\Classes;

class CsvReaderTest extends TestCase {

    /**
     * Import trait
     */
    use ImportTrait;

    /**
     * Filename
     * @var string
     */
    protected $fileName = 'files/test.csv';

    public function testSetSeparator()
    {
        $set = $this->loadedFile->setSeperator('-');
        $this->assertEquals('-', $set->getSeperator());
    }

    public function testSetDelimiter()
    {
        $set = $this->loadedFile->setDelimiter(';');
        $this->assertEquals(';', $set->getDelimiter());
    }

}