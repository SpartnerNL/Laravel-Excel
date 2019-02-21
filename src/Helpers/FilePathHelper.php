<?php

namespace Maatwebsite\Excel\Helpers;

use Illuminate\Support\Str;
use Illuminate\Http\UploadedFile;
use Illuminate\Contracts\Filesystem\Factory;
use Maatwebsite\Excel\Exceptions\UnreadableFileException;

class FilePathHelper
{
    /**
     * @var string
     */
    protected $tempPath;

    /**
     * @var string|null
     */
    protected $remoteTempDisk;

    /**
     * @var Factory
     */
    protected $filesystem;

    /**
     * @param Factory $filesystem
     */
    public function __construct(Factory $filesystem)
    {
        $this->filesystem     = $filesystem;
        $this->tempPath       = config('excel.temp_path', sys_get_temp_dir());
        $this->remoteTempDisk = config('excel.remote_temp_disk');
    }

    /**
     * @param string|UploadedFile $filePath
     * @param string|null         $disk
     * @param bool                $remote
     *
     * @throws \Illuminate\Contracts\Filesystem\FileNotFoundException
     * @return string
     */
    public function copyToTempFile($filePath, string $disk = null, bool $remote = false): string
    {
        $remote = $remote && $this->remoteTempDisk !== null;
        $fileName = $this->generateTempFileName($remote);
        $destination = $this->getTempPath($fileName);

        if ($filePath instanceof UploadedFile) {
            $filePath->move($destination);
        } elseif ($disk === null && realpath($filePath) !== false) {
            copy($filePath, $destination);
        } else {
            $this->copyFromDisk($filePath, $destination, $disk);
        }

        if ($remote) {
            $this->storeToTempDisk($fileName);
        }

        return $filePath;
    }

    /**
     * @param string $fileName
     */
    public function storeToTempDisk(string $fileName)
    {
        if ($this->remoteTempDisk === null) {
            return;
        }

        $readStream = fopen($this->getTempPath($fileName), 'rb+');

        $this->filesystem->disk($this->remoteTempDisk)->put($fileName, $readStream);

        fclose($readStream);
    }

    /**
     * @param bool $remote
     *
     * @return string
     */
    public function generateTempFileName(bool $remote = false): string
    {
        do {
            $fileName = 'laravel-excel-' . Str::random(16);
        } while (file_exists($this->getTempPath($fileName)) || ($remote && $this->filesystem->disk($this->remoteTempDisk)->exists($fileName)));

        return $fileName;
    }

    /**
     * @param string $fileName
     *
     * @throws \Illuminate\Contracts\Filesystem\FileNotFoundException
     * @throws UnreadableFileException
     * @return string
     */
    public function getTempFile(string $fileName): string
    {
        $file = $this->getTempPath($fileName);

        if (realpath($file) !== false) {
            return $file;
        }

        if ($this->remoteTempDisk !== null) {
            if ($this->copyFromDisk($fileName, $file, $this->remoteTempDisk) !== false) {
                return $file;
            }
        }

        throw new UnreadableFileException;
    }

    /**
     * @param string|null $fileName
     *
     * @return string
     */
    public function getTempPath(string $fileName = null): string
    {
        $tempPath = $this->tempPath;

        if ($fileName !== null) {
            $tempPath .= DIRECTORY_SEPARATOR . $fileName;
        }

        return $tempPath;
    }

    /**
     * @param string $fileName
     * @param bool $remote
     */
    public function deleteTempFile(string $fileName, bool $remote = false)
    {
        unlink($this->getTempPath($fileName));

        if ($remote && $this->remoteTempDisk !== null) {
            $this->filesystem->disk($this->remoteTempDisk)->delete($fileName);
        }
    }

    /**
     * @param string      $source
     * @param string      $destination
     * @param string|null $disk
     *
     * @throws \Illuminate\Contracts\Filesystem\FileNotFoundException
     * @return bool
     */
    protected function copyFromDisk(string $source, string $destination, string $disk = null): bool
    {
        $tmpStream = fopen($destination, 'wb+');

        $success = stream_copy_to_stream(
            $this->filesystem->disk($disk)->readStream($source),
            $tmpStream
        );

        fclose($tmpStream);

        return $success !== false;
    }
}
