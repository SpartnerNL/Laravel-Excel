<?php

use Mockery as m;

class RegisterFilterTestCase extends TestCase {

    public function setUp()
    {
        parent::setUp();
        $this->excel = app('excel');
    }

    public function testRegisterThroughConfig()
    {
        $registered = Config::get('excel::filters.registered');

        $filters = $this->excel->getFilters('registered');
        $this->assertEquals($registered, $filters);
    }

    public function testOnlyRegister()
    {
        $toRegister = array(
            'chunk' =>  'ChunkFilter'
        );

        $excel = $this->excel->registerFilters($toRegister);

        $filters = $this->excel->getFilters('registered');
        $this->assertEquals($toRegister, $filters);
    }

    public function testRegisterAndEnabled()
    {
        $toRegister = array(
            'registered'    =>  array(
                'chunk' =>  'ChunkFilter'
            ),
            'enabled'   =>  array(
                'chunk'
            )
        );

        $excel = $this->excel->registerFilters($toRegister);

        $filters = $this->excel->getFilters();
        $this->assertEquals($toRegister, $filters);

    }

    public function testEnableOneFilter()
    {
        $excel = $this->excel->filter('chunk');

        $filters = $this->excel->getFilters('enabled');
        $this->assertContains('chunk', $filters);
    }

    public function testEnableMultipleFilter()
    {
        $excel = $this->excel->filter(array('chunk', 'range'));

        $filters = $this->excel->getFilters('enabled');
        $this->assertContains('chunk', $filters);
        $this->assertContains('range', $filters);
    }

    public function testEnableFilterAndOverruleFilterClass()
    {
        $excel = $this->excel->filter('chunk', 'ChunkFilter');

        $registered = $this->excel->getFilters('registered');
        $this->assertEquals(array('chunk' => 'ChunkFilter'), $registered);

        $enabled    = $this->excel->getFilters('enabled');
        $this->assertContains('chunk', $enabled);

    }
}