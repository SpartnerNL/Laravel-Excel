<?php

namespace Maatwebsite\Excel\Bridge\Laravel;

use Maatwebsite\Excel\Configuration;
use Illuminate\Contracts\Config\Repository;

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

        $configuration->setReaderConfiguration($this->getReaderConfig());

        $configuration->setWriterConfiguration(new Configuration\WriterConfiguration(
            new Configuration\LaravelFilesystemConfiguration(
                $this->config->get('excel.writer.loader.driver', 'filesystem'),
                $this->config->get('excel.writer.loader.defaultDisk', 'local')
            )
        ));

        return $configuration;
    }

    /**
     * @return Configuration\ReaderConfiguration
     */
    public function getReaderConfig(): Configuration\ReaderConfiguration
    {
        $reader = new Configuration\ReaderConfiguration(
            new Configuration\LaravelFilesystemConfiguration(
                $this->config->get('excel.reader.loader.driver', 'filesystem'),
                $this->config->get('excel.reader.loader.defaultDisk', 'local')
            )
        );

        $reader->setHeadingRow($this->config->get('excel.reader.headingRow', false));

        return $reader;
    }
}
