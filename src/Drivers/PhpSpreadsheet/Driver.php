<?php

namespace Maatwebsite\Excel\Drivers\PhpSpreadsheet;

use Maatwebsite\Excel\Excel;
use Maatwebsite\Excel\Configuration;
use Illuminate\Filesystem\FilesystemManager;
use Illuminate\Contracts\Filesystem\Filesystem;
use Maatwebsite\Excel\Drivers\Driver as DriverInterface;
use Maatwebsite\Excel\Bridge\Laravel\Loaders\FilesystemLoader;
use Maatwebsite\Excel\Drivers\PhpSpreadsheet\Loaders\DefaultLoader;

class Driver implements DriverInterface
{
    /**
     * @var string
     */
    const DRIVER_NAME = 'phpspreadsheet';

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
            new Writer($this->configuration),
            new Reader($this->configuration, $this->getDefaultLoader())
        );
    }

    /**
     * @param FilesystemManager $filesystem
     *
     * @return Excel
     */
    public function buildLaravel(FilesystemManager $filesystem): Excel
    {
        $loader = $this->getLoaderForLaravel($filesystem);

        return new Excel(
            new Writer($this->configuration),
            new Reader($this->configuration, $loader)
        );
    }

    /**
     * @param FilesystemManager $filesystem
     *
     * @return callable
     */
    protected function getLoaderForLaravel(FilesystemManager $filesystem): callable
    {
        if ($this->configuration->getReaderConfiguration()->getFileStorage()->getDriver() === 'filesystem') {
            return $this->getFilesystemLoader($filesystem);
        }

        return $this->getDefaultLoader();
    }

    /**
     * @return DefaultLoader
     */
    protected function getDefaultLoader()
    {
        return new DefaultLoader();
    }

    /**
     * @param FilesystemManager $filesystem
     *
     * @return FilesystemLoader
     */
    protected function getFilesystemLoader(FilesystemManager $filesystem): FilesystemLoader
    {
        return new FilesystemLoader(
            $filesystem,
            $this->getDefaultLoader(),
            $this->configuration->getReaderConfiguration()->getFileStorage()->getDefaultDisk()
        );
    }
}
