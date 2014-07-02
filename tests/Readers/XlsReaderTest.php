<?php

require_once('traits/ImportTrait.php');
require_once('traits/SingleImportTestingTrait.php');

use Mockery as m;
use Maatwebsite\Excel\Readers\LaravelExcelReader;
use Maatwebsite\Excel\Classes;

class XlsReaderTest extends TestCase {

    /**
     * Import trait
     */
    use ImportTrait, SingleImportTestingTrait;

    /**
     * Filename
     * @var string
     */
    protected $fileName = 'files/test.xls';

}