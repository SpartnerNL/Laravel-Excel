<?php

namespace Maatwebsite\Excel\Bridge\Laravel;

use Illuminate\Contracts\Config\Repository;
use Maatwebsite\Excel\Configuration;

class LaravelConfigBridge
{
    /**
     * @var Repository
     */
    protected $config;

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

        $configuration->setReaderConfiguration(new Configuration\ReaderConfiguration(
            new Configuration\LaravelFilesystemConfiguration(
                $this->config->get('excel.reader.loader.driver', 'filesystem'),
                $this->config->get('excel.reader.loader.defaultDisk', 'local')
            )
        ));

        $configuration->setWriterConfiguration(new Configuration\WriterConfiguration(
            new Configuration\LaravelFilesystemConfiguration(
                $this->config->get('excel.writer.loader.driver', 'filesystem'),
                $this->config->get('excel.writer.loader.defaultDisk', 'local')
            )
        ));

        return $configuration;
    }
}
