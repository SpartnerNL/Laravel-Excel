<?php

use Mockery as m;

class TestConfig extends TestCase
{

    public function tearDown()
    {
        parent::tearDown();
        m::close();
    }

    public function testCreatorConfig()
    {
        $this->assertEquals(Config::get('excel::creator'), 'Maatwebsite');
    }

}