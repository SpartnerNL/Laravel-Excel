<?php

namespace Maatwebsite\Excel\Configuration;

class LaravelFilesystemConfiguration
{
    /**
     * @var string
     */
    protected $driver;

    /**
     * @var string
     */
    protected $defaultDisk;

    /**
     * @var array
     */
    protected $drivers = [
        'native',
        'filesystem',
    ];

    /**
     * @param string $driver
     * @param string $defaultDisk
     */
    public function __construct(string $driver = 'native', string $defaultDisk = 'local')
    {
        $this->setDriver($driver);
        $this->setDefaultDisk($defaultDisk);
    }

    /**
     * @return string
     */
    public function getDriver(): string
    {
        return $this->driver;
    }

    /**
     * @param string $driver
     *
     * @throws \InvalidArgumentException
     *
     * @return $this
     */
    public function setDriver(string $driver)
    {
        if (isset($this->drivers[$driver])) {
            throw new \InvalidArgumentException(sprintf('Unknown Filesystem driver [%s]', $driver));
        }

        $this->driver = $driver;

        return $this;
    }

    /**
     * @return string
     */
    public function getDefaultDisk(): string
    {
        return $this->defaultDisk;
    }

    /**
     * @param string $defaultDisk
     *
     * @return $this
     */
    public function setDefaultDisk(string $defaultDisk)
    {
        $this->defaultDisk = $defaultDisk;

        return $this;
    }
}
