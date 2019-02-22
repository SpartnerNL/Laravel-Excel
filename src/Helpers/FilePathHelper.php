<?php

namespace Maatwebsite\Excel\Helpers;

use Illuminate\Support\Str;
use Illuminate\Http\UploadedFile;
use Illuminate\Contracts\Filesystem\Factory;
use Maatwebsite\Excel\Exceptions\UnreadableFileException;
use Maatwebsite\Excel\Files\TemporaryFile;
use Maatwebsite\Excel\Files\TemporaryFileFactory;

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
     * @var TemporaryFileFactory
     */
    private $temporaryFileFactory;

    /**
     * @param Factory              $filesystem
     * @param TemporaryFileFactory $temporaryFileFactory
     */
    public function __construct(Factory $filesystem, TemporaryFileFactory $temporaryFileFactory)
    {
        $this->filesystem = $filesystem;
        // TODO: Remove support for excel.exports.temp_path in v4
        $this->tempPath             = config('excel.temp_path', config('excel.exports.temp_path', sys_get_temp_dir()));
        $this->remoteTempDisk       = config('excel.remote_temp_disk');
        $this->temporaryFileFactory = $temporaryFileFactory;
    }

    /**
     * @param string|UploadedFile $filePath
     * @param string|null         $disk
     * @param bool                $remote
     *
     * @throws \Illuminate\Contracts\Filesystem\FileNotFoundException
     * @return TemporaryFile
     */
    public function copyToTempFile($filePath, string $disk = null, bool $remote = false): TemporaryFile
    {
        $temporaryFile = $this->temporaryFileFactory->make();
        $path          = $temporaryFile->getLocalPath();

        if ($filePath instanceof UploadedFile) {
            $filePath->move(dirname($path), basename($path));
        } else if ($disk === null && realpath($filePath) !== false) {
            copy($filePath, $path);
        } else {
            $this->copyFromDisk($filePath, $path, $disk);
        }

        if ($remote) {
            $temporaryFile->store();
        }

        return $temporaryFile;
    }

    /**
     * @param string $fileName
     */
    public function storeToTempDisk(string $fileName)
    {
        if ($this->remoteTempDisk === null) {
            return;
        }

        $this->storeToDisk($this->getTempPath($fileName), $fileName, $this->remoteTempDisk);
    }

    /**
     * @param string      $source
     * @param string      $destination
     * @param string|null $disk
     * @param mixed       $diskOptions
     *
     * @return bool
     */
    public function storeToDisk(string $source, string $destination, string $disk = null, $diskOptions = []): bool
    {
        $readStream = fopen($source, 'rb+');

        $success = $this->filesystem->disk($disk)->put($destination, $readStream, $diskOptions);

        fclose($readStream);

        return $success;
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
        } while ($this->tempFileExists($fileName, $remote));

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

        if (realpath($file) !== false || ($this->remoteTempDisk !== null && $this->copyFromDisk($fileName, $file, $this->remoteTempDisk))) {
            return $file;
        }

        throw new UnreadableFileException();
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
     * @param bool   $remote
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

    /**
     * @param string $fileName
     * @param bool   $remote
     *
     * @return bool
     */
    protected function tempFileExists(string $fileName, bool $remote = false): bool
    {
        $fileExists = realpath($this->getTempPath($fileName)) !== false;

        if ($remote && $this->remoteTempDisk !== null) {
            $fileExists = $fileExists && $this->filesystem->disk($this->remoteTempDisk)->exists($fileName);
        }

        return $fileExists;
    }
}
