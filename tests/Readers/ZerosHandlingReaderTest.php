<?php

require_once('traits/ImportTrait.php');
require_once('traits/SingleImportTestingTrait.php');

use Mockery as m;

class ZerosHandlingReaderTest extends TestCase {

    /**
     * Traits
     */
    use ImportTrait;

    /**
     * Filename
     * @var string
     */
    protected $fileName = 'files/zeros.xls';

    /**
     * @var bool
     */
    protected $noHeadings = false;


    public function testDefaultGet()
    {
        $got = $this->loadedFile->get();
        $this->assertInstanceOf('Maatwebsite\Excel\Collections\RowCollection', $got);
        $this->assertCount(5, $got);
    }
    

    public function testStringsAppendedPrependedWithZeros()
    {
        $got = $this->loadedFile->toArray();

        $this->assertContains('TEST000', $got[3]);
        $this->assertContains('000TEST', $got[4]);
    }

}