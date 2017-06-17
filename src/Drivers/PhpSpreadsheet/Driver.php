<?php

namespace Maatwebsite\Excel\Drivers\PhpSpreadsheet;

use Maatwebsite\Excel\Configuration;
use Maatwebsite\Excel\Drivers\Driver as DriverInterface;
use Maatwebsite\Excel\Excel;

class Driver implements DriverInterface
{
    /**
     * @var string
     */
    const DRIVER_NAME = 'phpspreadsheet';

    /**
     * @var Configuration
     */
    private $configuration;

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
            new Reader()
        );
    }
}