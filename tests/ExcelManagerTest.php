<?php

namespace Maatwebsite\Excel\Tests;

use Maatwebsite\Excel\Configuration;
use Maatwebsite\Excel\Drivers\PhpSpreadsheet;
use Maatwebsite\Excel\Drivers\Spout;
use Maatwebsite\Excel\Excel;
use Maatwebsite\Excel\ExcelManager;
use PHPUnit\Framework\TestCase;

class ExcelManagerTest extends TestCase
{
    /**
     * @var ExcelManager
     */
    protected $manager;

    public function setUp()
    {
        parent::setUp();

        $this->manager = new ExcelManager(
            new Configuration()
        );
    }

    /**
     * @test
     */
    public function manager_resolves_phpspreadsheet_as_default_driver()
    {
        $driver = $this->manager->get();

        $this->assertInstanceOf(Excel::class, $driver);
    }

    /**
     * @test
     */
    public function can_set_default_through_configuration()
    {
        $configuration = new Configuration();
        $configuration->setDefaultDriver(Spout\Driver::DRIVER_NAME);

        $manager = new ExcelManager($configuration);

        $driver = $manager->get();

        $this->assertInstanceOf(Excel::class, $driver);
    }

    /**
     * @test
     */
    public function manager_can_resolve_default_driver()
    {
        $this->manager->setDefault(Spout\Driver::DRIVER_NAME);

        $driver = $this->manager->get();

        $this->assertInstanceOf(Excel::class, $driver);
    }

    /**
     * @test
     */
    public function manager_can_resolve_phpspreadsheet_driver()
    {
        $driver = $this->manager->get(PhpSpreadsheet\Driver::DRIVER_NAME);

        $this->assertInstanceOf(Excel::class, $driver);
    }

    /**
     * @test
     */
    public function manager_can_resolve_spout_driver()
    {
        $driver = $this->manager->get(Spout\Driver::DRIVER_NAME);

        $this->assertInstanceOf(Excel::class, $driver);
    }

    /**
     * @test
     * @expectedException \InvalidArgumentException
     * @expectedExceptionMessage Driver [unknown] not found or not added to the ExcelManager.
     */
    public function manager_will_throw_exception_when_driver_not_exists()
    {
        $this->manager->get('unknown');
    }

    /**
     * @test
     * @expectedException \InvalidArgumentException
     * @expectedExceptionMessage Driver [] not found or not added to the ExcelManager.
     */
    public function manager_will_throw_exception_when_resolving_default_without_having_default()
    {
        $this->manager->setDefault('');

        $this->manager->get();
    }

    /**
     * @test
     */
    public function manager_can_add_custom_driver_resolvers()
    {
        $this->manager->add('custom', function () {
            return $this
                ->getMockBuilder(Excel::class)
                ->disableOriginalConstructor()
                ->getMock();
        });

        $driver = $this->manager->get('custom');

        $this->assertInstanceOf(Excel::class, $driver);
    }
}
