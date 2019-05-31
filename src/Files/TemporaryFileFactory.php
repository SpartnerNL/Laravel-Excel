<?php

namespace Maatwebsite\Excel\Files;

use Illuminate\Support\Str;

class TemporaryFileFactory
{
    /**
     * @var string|null
     */
    private $temporaryPath;

    /**
     * @var string|null
     */
    private $temporaryDisk;

    /**
     * @var string|null
     */
    private $fileExtension;

   /**
     * @param string|null $temporaryPath
     * @param string|null $temporaryDisk
     */
    public function __construct(string $temporaryPath = null, string $temporaryDisk = null)
    {
        $this->temporaryPath = $temporaryPath;
        $this->temporaryDisk = $temporaryDisk;
    }

    /**
     * @param string|null $fileExtension
     */
    public function setFileExtension(string $fileExtension)
    {
        $this->fileExtension = $fileExtension;
    }

    /**
     * @return TemporaryFile
     */
    public function make(): TemporaryFile
    {
        if (null !== $this->temporaryDisk) {
            return $this->makeRemote();
        }

        return $this->makeLocal();
    }

    /**
     * @param string|null $fileName
     *
     * @return LocalTemporaryFile
     */
    public function makeLocal(string $fileName = null): LocalTemporaryFile
    {
        if (!file_exists($this->temporaryPath) && !mkdir($concurrentDirectory = $this->temporaryPath) && !is_dir($concurrentDirectory)) {
            throw new \RuntimeException(sprintf('Directory "%s" was not created', $concurrentDirectory));
        }

        return new LocalTemporaryFile(
            $this->temporaryPath . DIRECTORY_SEPARATOR . ($fileName ?: $this->generateFilename())
        );
    }

    /**
     * @return RemoteTemporaryFile
     */
    private function makeRemote(): RemoteTemporaryFile
    {
        $filename = $this->generateFilename();

        return new RemoteTemporaryFile(
            $this->temporaryDisk,
            $filename,
            $this->makeLocal($filename)
        );
    }

    /**
     * @return string
     */
    private function generateFilename(): string
    {
        $fileExtension = $this->fileExtension ? '.' . $this->fileExtension : '';

        return 'laravel-excel-' . Str::random(32) . $fileExtension;
    }
}
