<?php

namespace Maatwebsite\Excel\Files;

class LocalTemporaryFile extends TemporaryFile
{
    /**
     * @var string
     */
    private $filePath;

    /**
     * @param string $filePath
     */
    public function __construct(string $filePath)
    {
        touch($filePath);

        $this->filePath = realpath($filePath);
    }

    /**
     * @return string
     */
    public function getLocalPath(): string
    {
        return $this->filePath;
    }

    /**
     * @return bool
     */
    public function exists(): bool
    {
        return file_exists($this->filePath);
    }

    /**
     * @return bool
     */
    public function delete(): bool
    {
        return unlink($this->filePath);
    }

    public function store()
    {
        //
    }
}