<?php

namespace Maatwebsite\Excel\Files;

class LocalTemporaryFile extends TemporaryFile
{
    private readonly string $filePath;

    public function __construct(string $filePath)
    {
        touch($filePath);
        if (($rights = config('excel.temporary_files.local_permissions.file')) !== null) {
            chmod($filePath, $rights);
        }

        $this->filePath = realpath($filePath);
    }

    public function getLocalPath(): string
    {
        return $this->filePath;
    }

    public function exists(): bool
    {
        return file_exists($this->filePath);
    }

    public function delete(): bool
    {
        if (@unlink($this->filePath) || !$this->exists()) {
            return true;
        }

        return unlink($this->filePath);
    }

    /**
     * @return resource
     */
    public function readStream()
    {
        return fopen($this->getLocalPath(), 'rb+');
    }

    public function contents(): string
    {
        return file_get_contents($this->filePath);
    }

    /**
     * @param  string|resource  $contents
     */
    public function put($contents): void
    {
        file_put_contents($this->filePath, $contents);
    }
}
