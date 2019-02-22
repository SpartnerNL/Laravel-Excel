<?php

namespace Maatwebsite\Excel\Files;

use Illuminate\Contracts\Filesystem\Filesystem;

class Disk
{
    /**
     * @var Filesystem
     */
    protected $disk;

    /**
     * @var string|null
     */
    protected $name;

    /**
     * @var array
     */
    protected $diskOptions;

    /**
     * @param Filesystem  $disk
     * @param string|null $name
     * @param array       $diskOptions
     */
    public function __construct(Filesystem $disk, string $name = null, array $diskOptions = [])
    {
        $this->disk        = $disk;
        $this->name        = $name;
        $this->diskOptions = $diskOptions;
    }

    /**
     * @param string $source
     * @param string $destination
     *
     * @return bool
     */
    public function put(string $source, string $destination): bool
    {
        $readStream = fopen($source, 'rb+');

        $success = $this->disk->put($destination, $readStream, $this->diskOptions);

        fclose($readStream);

        return $success;
    }

    /**
     * @param string $fileName
     *
     * @return LocalTemporaryFile
     */
    public function copyToLocalTempFolder(string $fileName): LocalTemporaryFile
    {
        $temporaryFile = $this->getTemporaryFileFactory()->makeLocalTemporaryFile(
            $fileName
        );

        if ($temporaryFile->exists()) {
            return $temporaryFile;
        }

        $tmpStream = fopen($temporaryFile->getPath(), 'wb+');

        stream_copy_to_stream(
            $this->disk->readStream($fileName),
            $tmpStream
        );

        fclose($tmpStream);

        return $temporaryFile;
    }

    /**
     * @param string $path
     *
     * @return bool
     */
    public function exists(string $path): bool
    {
        return $this->disk->exists($path);
    }

    /**
     * @param string $filename
     *
     * @return bool
     */
    public function delete(string $filename): bool
    {
        return $this->disk->delete($filename);
    }

    /**
     * @return TemporaryFileFactory
     */
    private function getTemporaryFileFactory(): TemporaryFileFactory
    {
        return app(TemporaryFileFactory::class);
    }
}