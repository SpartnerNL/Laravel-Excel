<?php

include_once 'classes/TestExport.php';
include_once 'classes/TestExportHandler.php';
include_once 'classes/TestNewFile.php';
include_once 'classes/TestNewFileHandler.php';

class NewExcelFileTest extends TestCase {


    public function testInit()
    {
        $exporter = app('TestExport');
        $this->assertInstanceOf(\Maatwebsite\Excel\Files\NewExcelFile::class, $exporter);
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
        $this->assertInstanceOf(\Maatwebsite\Excel\Writers\LaravelExcelWriter::class, $exporter->getFileInstance());
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

        $exporter = app('TestNewFile');
        $result = $exporter->handleExport();

        $this->assertEquals('exported', $result);
    }

}