<?php

namespace Maatwebsite\Excel\Files;

use Illuminate\Support\Str;

class TemporaryFileFactory
{
    /**
     * @var Filesystem
     */
    private $filesystem;

    /**
     * @var string|null
     */
    private $temporaryPath;

    /**
     * @var string|null
     */
    private $temporaryDisk;

    /**
     * @param Filesystem  $filesystem
     * @param string|null $temporaryPath
     * @param string|null $temporaryDisk
     */
    public function __construct(Filesystem $filesystem, string $temporaryPath = null, string $temporaryDisk = null)
    {
        $this->filesystem    = $filesystem;
        $this->temporaryPath = $temporaryPath;
        $this->temporaryDisk = $temporaryDisk;
    }

    /**
     * @return TemporaryFile
     */
    public function make(): TemporaryFile
    {
        if (null !== $this->temporaryDisk) {
            return $this->makeRemoteTemporaryFile();
        }

        return $this->makeLocalTemporaryFile();
    }

    /**
     * @param string|null $fileName
     *
     * @return LocalTemporaryFile
     */
    public function makeLocalTemporaryFile(string $fileName = null): LocalTemporaryFile
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
    private function makeRemoteTemporaryFile(): RemoteTemporaryFile
    {
        return new RemoteTemporaryFile(
            $this->filesystem->disk($this->temporaryDisk),
            $this->generateFilename()
        );
    }

    /**
     * @return string
     */
    private function generateFilename(): string
    {
        return 'laravel-excel-' . Str::random(32);
    }
}