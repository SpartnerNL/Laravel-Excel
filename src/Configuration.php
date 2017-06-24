<?php

namespace Maatwebsite\Excel;

use Maatwebsite\Excel\Drivers\PhpSpreadsheet\Driver;
use Maatwebsite\Excel\Configuration\ReaderConfiguration;
use Maatwebsite\Excel\Configuration\WriterConfiguration;
use Maatwebsite\Excel\Configuration\LaravelFilesystemConfiguration;

class Configuration
{
    /**
     * @var string
     */
    protected $defaultDriver = Driver::DRIVER_NAME;

    /**
     * @var ReaderConfiguration
     */
    protected $reader;

    /**
     * @var WriterConfiguration
     */
    protected $writer;

    /**
     * Configuration class.
     */
    public function __construct()
    {
        $this->reader = new ReaderConfiguration(
            new LaravelFilesystemConfiguration('native', 'local')
        );

        $this->writer = new WriterConfiguration(
            new LaravelFilesystemConfiguration('native', 'local')
        );
    }

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

    /**
     * @return ReaderConfiguration
     */
    public function getReaderConfiguration(): ReaderConfiguration
    {
        return $this->reader;
    }

    /**
     * @param ReaderConfiguration $reader
     *
     * @return Configuration
     */
    public function setReaderConfiguration(ReaderConfiguration $reader): Configuration
    {
        $this->reader = $reader;

        return $this;
    }

    /**
     * @return WriterConfiguration
     */
    public function getWriterConfiguration(): WriterConfiguration
    {
        return $this->writer;
    }

    /**
     * @param WriterConfiguration $writer
     *
     * @return Configuration
     */
    public function setWriterConfiguration(WriterConfiguration $writer): Configuration
    {
        $this->writer = $writer;

        return $this;
    }
}
