<?php

use Mockery as m;

class ChunkReadFilterTest extends TestCase {

    public function setUp()
    {
        parent::setUp();
        $this->excel = app('excel');
    }



    public function testCanChunkXls()
    {
    	$this->assertCanChunkIntoGroups(1,"sample.xls",20);
    	$this->assertCanChunkIntoGroups(2,"sample.xls",15);
    	$this->assertCanChunkIntoGroups(2,"sample.xls",10);
    	$this->assertCanChunkIntoGroups(4,"sample.xls",5);
    }    

    public function testCanChunkXlsx()
    {
    	$this->assertCanChunkIntoGroups(1,"sample.xlsx",20);
    	$this->assertCanChunkIntoGroups(2,"sample.xlsx",15);
    	$this->assertCanChunkIntoGroups(2,"sample.xlsx",10);
    	$this->assertCanChunkIntoGroups(4,"sample.xlsx",5);
    }    

    public function testCanChunkCsv()
    {
    	$this->assertCanChunkIntoGroups(1,"sample.csv", 20);
    	$this->assertCanChunkIntoGroups(2,"sample.csv", 15);
    	$this->assertCanChunkIntoGroups(2,"sample.csv", 10);
    	$this->assertCanChunkIntoGroups(4,"sample.csv", 5);
    }

    private function assertCanChunkIntoGroups($expected_rounds, $file, $size)
    {
    	$output = [];
        
        $rounds = 0;

        $this->excel->filter('chunk')->load(__DIR__ . "/files/{$file}")->chunk($size,function($results) use (&$output, &$rounds){
        	$rounds++;
        	foreach ($results as $row) {
        		$output[] = (int) $row->header;
        	}
        });

        $expected = "1,2,3,4,5,6,7,8,9,10,11,12,13,14,15";

        $this->assertEquals($expected, implode(",", $output ));
        $this->assertEquals($expected_rounds, $rounds);

    }
}