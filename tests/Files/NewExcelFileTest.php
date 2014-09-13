<?php

include_once 'classes/TestExport.php';
include_once 'classes/TestExportHandler.php';

class NewExcelFileTest extends TestCase {


    public function testInit()
    {
        $exporter = app('TestExport');
        $this->assertInstanceOf('Maatwebsite\Excel\Files\NewExcelFile', $exporter);
    }


    public function testGetFilename()
    {
        $exporter = app('TestExport');
        $this->assertEquals('test-file', $exporter->getFilename());
    }


    public function testCreateNewFile()
    {
        $exporter = app('TestExport');
        $exporter->createNewFile();
        $this->assertInstanceOf('Maatwebsite\Excel\Writers\LaravelExcelWriter', $exporter->getFileInstance());
    }


    public function testDirectUsage()
    {
        $exporter = app('TestExport');
        $exporter->setTitle('New title');

        $this->assertEquals('New title', $exporter->getFileInstance()->getTitle());
    }


    public function testExportHandler()
    {
        $exporter = app('TestExport');
        $result = $exporter->handleExport();

        $this->assertEquals('exported', $result);
    }

}