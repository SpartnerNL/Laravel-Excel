<?php

namespace Maatwebsite\Excel\Helpers;

use Illuminate\Contracts\Filesystem\Factory;
use Illuminate\Http\UploadedFile;

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
     */
    protected function copyToTempFile(string $source, string $destination, string $disk = null)
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
     * @return string
     */
    protected function generateTemporaryFile(): string
    {
        return $this->tempPath . DIRECTORY_SEPARATOR . str_random(16);
    }
}