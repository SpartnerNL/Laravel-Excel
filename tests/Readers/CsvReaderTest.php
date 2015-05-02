<?php

require_once('traits/ImportTrait.php');
require_once('traits/SingleImportTestingTrait.php');

use Mockery as m;
use Maatwebsite\Excel\Readers\LaravelExcelReader;
use Maatwebsite\Excel\Classes;

class CsvReaderTest extends TestCase {

    /**
     * Import trait
     */
    use ImportTrait, SingleImportTestingTrait;

    /**
     * Filename
     * @var string
     */
    protected $fileName = 'files/test.csv';


    public function testSeparator()
    {
        $this->assertEquals('_', $this->loadedFile->getSeparator());
    }


    public function testSetSeparator()
    {
        $set = $this->loadedFile->setSeparator('-');
        $this->assertEquals('-', $set->getSeparator());
    }


    public function testSetDelimiter()
    {
        $this->loadedFile->setDelimiter(';');
        $this->reload();
        $this->assertEquals(';', $this->loadedFile->getDelimiter());
    }


    public function testSetEnclosure()
    {
        $this->loadedFile->setEnclosure('d');
        $this->reload();
        $this->assertEquals('d', $this->loadedFile->getEnclosure());
    }
}