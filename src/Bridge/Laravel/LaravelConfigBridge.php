<?php

namespace Maatwebsite\Excel\Bridge\Laravel;

use Illuminate\Contracts\Config\Repository;
use Maatwebsite\Excel\Configuration;

class LaravelConfigBridge
{
    /**
     * @var Repository
     */
    private $config;

    /**
     * @param Repository $config
     */
    public function __construct(Repository $config)
    {
        $this->config = $config;
    }

    /**
     * @return Configuration
     */
    public function toConfiguration(): Configuration
    {
        $configuration = new Configuration();

        $configuration->setDefaultDriver(
            $this->config->get('excel.driver', 'phpspreadsheet')
        );

        return $configuration;
    }
}
