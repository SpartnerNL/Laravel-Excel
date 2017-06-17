<?php

namespace Maatwebsite\Excel;

use Maatwebsite\Excel\Drivers\PhpSpreadsheet\Driver;

class Configuration
{
    /**
     * @var string
     */
    protected $defaultDriver = Driver::DRIVER_NAME;

    /**
     * @param string $defaultDriver
     *
     * @return $this
     */
    public function setDefaultDriver(string $defaultDriver)
    {
        $this->defaultDriver = $defaultDriver;

        return $this;
    }

    /**
     * @return string
     */
    public function getDefaultDriver(): string
    {
        return $this->defaultDriver;
    }
}