<?php

require_once('traits/ImportTrait.php');
require_once('traits/SingleImportTestingTrait.php');

class XlsReaderTest extends TestCase
{
    /**
     * Import trait.
     */
    use ImportTrait, SingleImportTestingTrait;

    /**
     * Filename.
     * @var string
     */
    protected $fileName = 'files/test.xls';
}
