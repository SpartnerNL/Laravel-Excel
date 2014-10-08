<?php

use Orchestra\Testbench\TestCase as TestBenchTestCase;

class TestCase extends TestBenchTestCase
{

    public function testExcelClass()
    {
        $excel = App::make('Maatwebsite\Excel\Excel');
        $this->assertInstanceOf('Maatwebsite\Excel\Excel', $excel);
    }


    protected function getPackageProviders()
    {
        return array('Maatwebsite\Excel\ExcelServiceProvider');
    }


    protected function getPackagePath()
    {
        return realpath(implode(DIRECTORY_SEPARATOR, array(
            __DIR__,
            '..',
            'src',
            'Maatwebsite',
            'Excel'
        )));
    }

}