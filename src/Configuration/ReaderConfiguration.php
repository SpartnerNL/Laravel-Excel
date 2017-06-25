<?php

namespace Maatwebsite\Excel\Configuration;

class ReaderConfiguration
{
    /**
     * @var LaravelFilesystemConfiguration
     */
    protected $fileStorage;

    /**
     * @var bool
     */
    protected $headingRow = false;

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

    /**
     * @param int|false $row
     *
     * @return $this
     */
    public function setHeadingRow($row)
    {
        $this->headingRow = $row;

        return $this;
    }

    /**
     * @return int|false
     */
    public function getHeadingRow()
    {
        return $this->headingRow;
    }

    /**
     * @return bool
     */
    public function hasHeadingRow(): bool
    {
        return $this->headingRow !== false;
    }
}
