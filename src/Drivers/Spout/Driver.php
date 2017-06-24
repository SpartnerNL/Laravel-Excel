<?php

namespace Maatwebsite\Excel\Drivers\Spout;

use Maatwebsite\Excel\Configuration;
use Maatwebsite\Excel\Drivers\Driver as DriverInterface;
use Maatwebsite\Excel\Excel;

class Driver implements DriverInterface
{
    /**
     * @var string
     */
    const DRIVER_NAME = 'spout';

    /**
     * @var Configuration
     */
    protected $configuration;

    /**
     * @param Configuration $configuration
     */
    public function __construct(Configuration $configuration)
    {
        $this->configuration = $configuration;
    }

    /**
     * @return Excel
     */
    public function build(): Excel
    {
        return new Excel(
            new Writer(),
            new Reader(new Configuration())
        );
    }
}
