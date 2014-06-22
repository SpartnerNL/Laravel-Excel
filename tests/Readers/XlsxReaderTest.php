<?php

require_once('traits/ImportTrait.php');

use Mockery as m;
use Maatwebsite\Excel\Readers\LaravelExcelReader;
use Maatwebsite\Excel\Classes;

class XlsxReaderTest extends TestCase {

    /**
     * Import trait
     */
    use ImportTrait;

    /**
     * Filename
     * @var string
     */
    protected $fileName = 'files/test.xlsx';

}