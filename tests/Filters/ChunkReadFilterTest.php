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
        $this->assertCanChunkIntoGroups(1,"sample.xls",15);
        $this->assertCanChunkIntoGroups(2,"sample.xls",10);
        $this->assertCanChunkIntoGroups(3,"sample.xls",5);
        $this->assertCanChunkIntoGroups(15,"sample.xls",1);
    }


    public function testCanChunkXlsx()
    {
    	$this->assertCanChunkIntoGroups(1,"sample.xlsx",20);
    	$this->assertCanChunkIntoGroups(1,"sample.xlsx",15);
    	$this->assertCanChunkIntoGroups(2,"sample.xlsx",10);
        $this->assertCanChunkIntoGroups(3,"sample.xlsx",5);
    	$this->assertCanChunkIntoGroups(15,"sample.xlsx",1);
    }


    public function testCanChunkCsv()
    {
    	$this->assertCanChunkIntoGroups(1,"sample.csv", 20);
    	$this->assertCanChunkIntoGroups(1,"sample.csv", 15);
    	$this->assertCanChunkIntoGroups(2,"sample.csv", 10);
        $this->assertCanChunkIntoGroups(3,"sample.csv", 5);
    	$this->assertCanChunkIntoGroups(15,"sample.csv", 1);
    }


    public function testCanChunkMultipleSheets()
    {
        $output = [];

        $rounds = 0;

        // test with small chunks
        $chunk_size = 2;
        $expected = "1,3,5,7,9,11,13,15,17,19,1,3,5,7,9,11,13,15,17,19";
        $expected_chunks = 10;

        // Sheet2 has more rows than sheet 1
        $this->excel->filter('chunk')->selectSheets('Sheet2')->load(__DIR__ . "/files/multi.xls")->chunk($chunk_size,function($results) use (&$output, &$rounds){
            $rounds++;
            foreach ($results as $row) {
                $output[] = (int) $row->header;
            }
        });

        $this->assertEquals($expected, implode(",", $output ), "Chunked ($chunk_size) value not equal with source data.");
        $this->assertEquals($expected_chunks, $rounds, "Expecting total chunks is $expected_chunks when chunk with size $chunk_size");

    }


    private function assertCanChunkIntoGroups($expected_chunks, $file, $chunk_size)
    {
    	$output = [];
        
        $rounds = 0;

        $this->excel->filter('chunk')->load(__DIR__ . "/files/{$file}")->chunk($chunk_size,function($results) use (&$output, &$rounds){
        	$rounds++;
            foreach ($results as $row) {
        		$output[] = (int) $row->header;
        	}
        });

        $expected = "1,2,3,4,5,6,7,8,9,10,11,12,13,14,15";

        $this->assertEquals($expected, implode(",", $output ), "Chunked ($chunk_size) value not equal with source data.");
        $this->assertEquals($expected_chunks, $rounds, "Expecting total chunks is $expected_chunks when chunk with size $chunk_size");

    }
}