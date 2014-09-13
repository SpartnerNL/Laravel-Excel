<?php

include_once 'classes/TestImport.php';
include_once 'classes/TestImportHandler.php';

class ExcelFileTest extends TestCase {


    public function testInit()
    {
        $importer = app('TestImport');
        $this->assertInstanceOf('Maatwebsite\Excel\Files\ExcelFile', $importer);
    }


    public function testGetFile()
    {
        $importer = app('TestImport');
        $file = $importer->getFile();
        $exploded = explode('/',$file);
        $filename = end($exploded);

        $this->assertEquals('test.csv', $filename);
    }


    public function testGetFilters()
    {
        $importer = app('TestImport');
        $this->assertContains('chunk', $importer->getFilters());
        $this->assertContains('chunk', $importer->getFileInstance()->filters['enabled']);
    }


    public function testLoadFile()
    {
        $importer = app('TestImport');
        $importer->loadFile();
        $this->assertInstanceOf('Maatwebsite\Excel\Readers\LaravelExcelReader', $importer->getFileInstance());
    }


    public function testGetResultsDirectly()
    {
        $importer = app('TestImport');
        $results = $importer->get();

        $this->assertInstanceOf('Maatwebsite\Excel\Collections\RowCollection', $results);
        $this->assertCount(5, $results);
    }


    public function testImportHandler()
    {
        $importer = app('TestImport');
        $results = $importer->handleImport();

        $this->assertInstanceOf('Maatwebsite\Excel\Collections\RowCollection', $results);
        $this->assertCount(5, $results);
    }


}