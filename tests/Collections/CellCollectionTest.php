<?php

use Maatwebsite\Excel\Collections\CellCollection;

class CellCollectionTest extends TestCase {


    public function __construct()
    {
        $this->collection = new CellCollection([
            'one' => 'one',
            'two' => 'two'
        ]);
    }


    public function testSetItems()
    {
        $this->collection->setItems([
            'three' => 'three'
        ]);

        $this->assertContains('three', $this->collection);
        $this->assertCount(3, $this->collection);
    }


    /**
     * @see https://github.com/Maatwebsite/Laravel-Excel/issues/823
     */
    public function testSetItemsWithNumericHeaders()
    {
        $this->collection->setItems([
            0 => 0.5,
            100 => 0.216,
        ]);

        $this->assertArrayHasKey(0, $this->collection);
        $this->assertArrayHasKey(100, $this->collection);
    }


    public function testDynamicGetters()
    {
        $this->assertEquals('two', $this->collection->two);
    }


    public function testIsset()
    {
        $this->assertTrue(isset($this->collection->two));
        $this->assertFalse(isset($this->collection->nonexisting));
    }


    public function testEmpty()
    {
        $this->assertNotEmpty($this->collection->two);
        $this->assertEmpty($this->collection->nonexisting);
    }


    public function testDynamicCheck()
    {
        $this->assertTrue($this->collection->two ? true : false);
        $this->assertFalse($this->collection->nonexisting ? true : false);
    }
}
