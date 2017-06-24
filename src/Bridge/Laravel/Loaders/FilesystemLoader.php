<?php

namespace Maatwebsite\Excel\Bridge\Laravel\Loaders;

use Illuminate\Filesystem\FilesystemManager;
use InvalidArgumentException;
use Maatwebsite\Excel\Exceptions\InvalidSpreadsheetLoaderException;
use PhpOffice\PhpSpreadsheet\Spreadsheet;

class FilesystemLoader
{
    /**
     * @var FilesystemManager
     */
    protected $manager;

    /**
     * @var callable
     */
    protected $defaultLoader;

    /**
     * @var string
     */
    private $defaultDisk;

    /**
     * @param FilesystemManager $manager
     * @param callable          $defaultLoader
     * @param string            $defaultDisk
     */
    public function __construct(FilesystemManager $manager, callable $defaultLoader, $defaultDisk = 'local')
    {
        $this->manager = $manager;
        $this->defaultLoader = $defaultLoader;
        $this->defaultDisk = $defaultDisk;
    }

    /**
     * @param string $filePath
     *
     * @return Spreadsheet
     */
    public function __invoke(string $filePath): Spreadsheet
    {
        $loader = $this->defaultLoader;

        $tmpFilePath = $this->getTmpFilePath();
        $tmpFile = $this->getTmpFile($tmpFilePath);

        list($diskName, $filePath) = $this->resolvePath($filePath);

        $inputStream = $this->loadFileFromDisk($diskName, $filePath);

        $this->copy($inputStream, $tmpFile);

        $spreadsheet = $loader($tmpFilePath);

        $this->deleteTmpFile($tmpFilePath);

        return $spreadsheet;
    }

    /**
     * @param resource|bool $inputStream
     * @param resource|bool $tmpStream
     */
    protected function copy($inputStream, $tmpStream)
    {
        stream_copy_to_stream($inputStream, $tmpStream);

        fclose($tmpStream);
    }

    /**
     * @param string $diskName
     * @param string $filePath
     *
     * @throws InvalidSpreadsheetLoaderException
     *
     * @return bool|resource
     */
    protected function loadFileFromDisk(string $diskName, string $filePath)
    {
        try {
            $disk = $this->manager->disk($diskName);
        } catch (InvalidArgumentException $e) {
            throw new InvalidSpreadsheetLoaderException(
                sprintf('Disk [%s] does not exist or has an invalid driver', $diskName),
                $e->getCode(),
                $e
            );
        }

        return $disk->readStream($filePath);
    }

    /**
     * @return string
     */
    public function getTmpFilePath(): string
    {
        return tempnam(sys_get_temp_dir(), 'laravel-excel');
    }

    /**
     * @param string $tmpFilePath
     *
     * @return bool|resource
     */
    protected function getTmpFile(string $tmpFilePath)
    {
        return fopen($tmpFilePath, 'w+b');
    }

    /**
     * @param string $tmpFile
     *
     * @return bool
     */
    protected function deleteTmpFile(string $tmpFile)
    {
        return unlink($tmpFile);
    }

    /**
     * @param string $filePath
     *
     * @return array
     */
    private function resolvePath(string $filePath)
    {
        if (strpos($filePath, '://') < 1) {
            return [$this->defaultDisk, $filePath];
        }

        return explode('://', $filePath, 2);
    }
}
