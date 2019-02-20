<?php

namespace Maatwebsite\Excel\Helpers;

use Illuminate\Support\Str;
use Illuminate\Http\UploadedFile;
use Illuminate\Contracts\Filesystem\Factory;

class FilePathHelper
{
    /**
     * @var string
     */
    protected $tempPath;

    /**
     * @var Factory
     */
    protected $filesystem;

    /**
     * @param Factory $filesystem
     * @param string  $tempPath
     */
    public function __construct(Factory $filesystem, string $tempPath)
    {
        $this->tempPath   = $tempPath;
        $this->filesystem = $filesystem;
    }

    /**
     * @param string|UploadedFile $filePath
     * @param string|null         $disk
     *
     * @return string
     *
     * @throws \Illuminate\Contracts\Filesystem\FileNotFoundException
     */
    public function getRealPath($filePath, string $disk = null): string
    {
        $destination = $this->generateTemporaryFile();

        if ($filePath instanceof UploadedFile) {
            return $filePath->move($destination)->getRealPath();
        }

        $this->copyToTempFile($filePath, $destination, $disk);

        return $destination;
    }

    /**
     * @param string      $source
     * @param string      $destination
     * @param string|null $disk
     *
     * @throws \Illuminate\Contracts\Filesystem\FileNotFoundException
     */
    public function copyToTempFile(string $source, string $destination, string $disk = null)
    {
        if (null === $disk && false !== realpath($source)) {
            copy($source, $destination);

            return;
        }

        $tmpStream = fopen($destination, 'wb+');

        stream_copy_to_stream(
            $this->filesystem->disk($disk)->readStream($source),
            $tmpStream
        );

        fclose($tmpStream);
    }

    /**
     * @param string $realPath
     * @param string $tempDisk
     *
     * @throws \Illuminate\Contracts\Filesystem\FileExistsException
     */
    public function storeToTempDisk(string $realPath, string $tempDisk)
    {
        $readStream = fopen($realPath, 'rb+');

        $this->filesystem->disk($tempDisk)->writeStream(basename($realPath), $readStream);

        fclose($readStream);
    }

    /**
     * @return string
     */
    protected function generateTemporaryFile(): string
    {
        return $this->tempPath . DIRECTORY_SEPARATOR . Str::random(16);
    }
}
