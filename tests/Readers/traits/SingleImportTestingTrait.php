<?php

trait SingleImportTestingTrait {


    public function testGet()
    {
        $got = $this->loadedFile->get();
        $this->assertInstanceOf('Maatwebsite\Excel\Collections\RowCollection', $got);
        $this->assertCount(5, $got);
    }


    public function testGetWithColumns()
    {
        $columns = array('heading_one', 'heading_two');
        $got = $this->loadedFile->get($columns);

        $this->assertInstanceOf('Maatwebsite\Excel\Collections\RowCollection', $got);
        $this->assertCount(5, $got);
    }


    public function testAll()
    {
        $all = $this->loadedFile->all();
        $this->assertInstanceOf('Maatwebsite\Excel\Collections\RowCollection', $all);
        $this->assertCount(5, $all);
    }


    public function testFirst()
    {
        $first = $this->loadedFile->first();
        $this->assertInstanceOf('Maatwebsite\Excel\Collections\CellCollection', $first);

        // 3 columns
        $this->assertCount(3, $first);
    }


    public function testFirstWithColumns()
    {
        $columns = array('heading_one', 'heading_two');
        $first = $this->loadedFile->first($columns);

        $this->assertInstanceOf('Maatwebsite\Excel\Collections\CellCollection', $first);
        $this->assertCount(count($columns), $first);
    }


    public function testEach()
    {
        $me = $this;

        $this->loadedFile->each(function($cells) use($me) {

            $me->assertInstanceOf('Maatwebsite\Excel\Collections\CellCollection', $cells);

        });
    }


    public function testToArray()
    {
        $array = $this->loadedFile->toArray();
        $this->assertEquals(array(

            array(
                'heading_one'  => 'test',
                'heading_two'  => 'test',
                'heading_three'  => 'test',
            ),
            array(
                'heading_one'  => 'test',
                'heading_two'  => 'test',
                'heading_three'  => 'test',
            ),
            array(
                'heading_one'  => 'test',
                'heading_two'  => 'test',
                'heading_three'  => 'test',
            ),
            array(
                'heading_one'  => 'test',
                'heading_two'  => 'test',
                'heading_three'  => 'test',
            ),
            array(
                'heading_one'  => 'test',
                'heading_two'  => 'test',
                'heading_three'  => 'test',
            )

        ), $array);
    }


    public function testImportedHeadingsSlugged()
    {
        $first = $this->loadedFile->first()->toArray();
        $keys  = array_keys($first);

        $this->assertEquals(array(
            'heading_one',
            'heading_two',
            'heading_three'
        ), $keys);
    }


    public function testImportedHeadingsHashed()
    {
        Config::set('excel.import.heading', 'hashed');

        $loaded = $this->reload();

        $first = $loaded->first()->toArray();
        $keys  = array_keys($first);

        $this->assertEquals(array(
            md5('heading one'),
            md5('heading two'),
            md5('heading three')
        ), $keys);
    }


    public function testImportedHeadingsNumeric()
    {
        Config::set('excel.import.heading', 'numeric');

        $loaded = $this->reload();

        $first = $loaded->first()->toArray();
        $keys  = array_keys($first);

        $this->assertEquals(array(
            0,
            1,
            2
        ), $keys);
    }


    public function testImportedHeadingsOriginal()
    {
        Config::set('excel.import.heading', 'original');

        $loaded = $this->reload();

        $first = $loaded->first()->toArray();
        $keys  = array_keys($first);

        $this->assertEquals(array(
            'heading one',
            'heading two',
            'heading three'
        ), $keys);
    }


    public function testRemember()
    {
        $remembered = $this->loadedFile->remember(10);

        $this->assertEquals($this->reader, $remembered);
        $this->assertEquals(10, $remembered->cacheMinutes);
        $this->assertTrue($remembered->remembered);
    }


    public function testByConfig()
    {
        $config = $this->loadedFile->byConfig('excel.import.sheets');
        $this->assertInstanceOf('Maatwebsite\Excel\Collections\SheetCollection', $config);
    }


    public function testByConfigCallback()
    {
        $me = $this;

        $config = $this->loadedFile->byConfig('excel.import.sheets', function($config) use($me)
        {
            $me->assertInstanceOf('Maatwebsite\Excel\Readers\ConfigReader', $config);
        });

        $this->assertInstanceOf('Maatwebsite\Excel\Collections\SheetCollection', $config);
    }


    public function testTake()
    {
        $taken = $this->loadedFile->take(2);
        $this->assertEquals(2, $taken->getLimit());
        $this->assertCount(2, $taken->get());
    }


    public function testSkip()
    {
        $taken = $this->loadedFile->skip(1);
        $this->assertEquals(1, $taken->getSkip());
        $this->assertCount(4, $taken->get());
    }


    public function testLimit()
    {
        $taken = $this->loadedFile->limit(2, 1);
        $this->assertEquals(2, $taken->getLimit());
        $this->assertEquals(1, $taken->getSkip());
        $this->assertCount(2, $taken->get());
    }


    public function testSelect()
    {
        $columns = array('heading_one', 'heading_two');

        $taken = $this->loadedFile->select($columns);
        $this->assertEquals($columns, $taken->columns);
    }


    public function testSetDateFormat()
    {
        $set = $this->loadedFile->setDateFormat('Y-m-d');
        $this->assertEquals('Y-m-d', $set->getDateFormat());
    }


    public function testFormatDates()
    {
        $set = $this->loadedFile->formatDates(true, 'Y-m-d');
        $this->assertTrue($set->needsDateFormatting());
        $this->assertEquals('Y-m-d', $set->getDateFormat());
    }


    public function testSetDateColumns()
    {
        $set = $this->loadedFile->setDateColumns('created_at', 'deleted_at');
        $this->assertTrue($set->needsDateFormatting());
        $this->assertEquals(array('created_at', 'deleted_at'), $set->getDateColumns());
    }


    public function testCalculate()
    {
        $set = $this->loadedFile->calculate();
        $this->assertTrue($set->needsCalculation());
    }


    public function testIgnoreEmpty()
    {
        $set = $this->loadedFile->ignoreEmpty();
        $this->assertTrue($set->needsIgnoreEmpty());
    }
}