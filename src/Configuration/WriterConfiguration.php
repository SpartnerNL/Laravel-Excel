<?php

namespace Maatwebsite\Excel\Configuration;

class WriterConfiguration
{
    /**
     * @var LaravelFilesystemConfiguration
     */
    protected $fileStorage;

    /**
     * @param LaravelFilesystemConfiguration $fileStorage
     */
    public function __construct(LaravelFilesystemConfiguration $fileStorage)
    {
        $this->fileStorage = $fileStorage;
    }

    /**
     * @return LaravelFilesystemConfiguration
     */
    public function getFileStorage(): LaravelFilesystemConfiguration
    {
        return $this->fileStorage;
    }

    /**
     * @param LaravelFilesystemConfiguration $fileStorage
     *
     * @return $this
     */
    public function setFileStorage(LaravelFilesystemConfiguration $fileStorage)
    {
        $this->fileStorage = $fileStorage;

        return $this;
    }
}