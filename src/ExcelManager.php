<?php

namespace Maatwebsite\Excel;

use InvalidArgumentException;
use Maatwebsite\Excel\Drivers\Driver;
use Maatwebsite\Excel\Drivers\PhpSpreadsheet;
use Maatwebsite\Excel\Drivers\Spout;

class ExcelManager
{
    /**
     * @var array
     */
    protected $drivers = [];

    /**
     * @var array
     */
    protected $resolvers = [];

    /**
     * @var string
     */
    protected $default = PhpSpreadsheet\Driver::DRIVER_NAME;

    /**
     * @param Configuration $configuration
     */
    public function __construct(Configuration $configuration)
    {
        $this->add(PhpSpreadsheet\Driver::DRIVER_NAME, function () use ($configuration) {
            return new PhpSpreadsheet\Driver($configuration);
        });

        $this->add(Spout\Driver::DRIVER_NAME, function () use ($configuration) {
            return new Spout\Driver($configuration);
        });

        $this->setDefault($configuration->getDefaultDriver());
    }

    /**
     * @param string|null $name
     *
     * @throws InvalidArgumentException
     *
     * @return Excel
     */
    public function get(string $name = null): Excel
    {
        $name = $name ?: $this->default;

        if (isset($this->drivers[$name])) {
            return $this->drivers[$name];
        }

        if (!isset($this->resolvers[$name])) {
            throw new InvalidArgumentException(
                sprintf('Driver [%s] not found or not added to the ExcelManager.', $name)
            );
        }

        $resolver = $this->resolvers[$name];
        $driver = $resolver();

        // If the resolver has a driver instance, let the driver build the class structure
        if ($driver instanceof Driver) {
            $driver = $driver->build();
        }

        return $this->drivers[$name] = $driver;
    }

    /**
     * @param string   $driver
     * @param callable $callback
     *
     * @return $this
     */
    public function add(string $driver, callable $callback)
    {
        $this->resolvers[$driver] = $callback;

        return $this;
    }

    /**
     * @param string $default
     */
    public function setDefault(string $default)
    {
        $this->default = $default;
    }
}
