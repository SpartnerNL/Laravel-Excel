<?php

use Orchestra\Testbench\TestCase as TestBenchTestCase;

class TestCase extends TestBenchTestCase
{

    /**
     * Test the binding with the ioC
     * @return [type] [description]
     */
    public function testExcelClass()
    {
        $excel = App::make('Maatwebsite\Excel\Excel');
        $this->assertInstanceOf('Maatwebsite\Excel\Excel', $excel);
    }

    /**
     * Get the package service provider
     * @return [type] [description]
     */
    protected function getPackageProviders()
    {
        return array('Maatwebsite\Excel\ExcelServiceProvider');
    }

    /**
     * Get the path for this package
     *
     * @return string
     */
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