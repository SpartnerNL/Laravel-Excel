<?php

namespace Maatwebsite\Excel\Files;

use Illuminate\Contracts\Filesystem\FileNotFoundException;
use Symfony\Component\HttpFoundation\File\UploadedFile;

abstract class TemporaryFile
{
    abstract public function getLocalPath(): string;

    abstract public function exists(): bool;

    /**
     * @param  string|resource  $contents
     */
    abstract public function put($contents): void;

    abstract public function delete(): bool;

    /**
     * @return resource
     */
    abstract public function readStream();

    abstract public function contents(): string;

    public function sync(): TemporaryFile
    {
        return $this;
    }

    /**
     * @throws FileNotFoundException
     */
    public function copyFrom(string|UploadedFile $filePath, ?string $disk = null): TemporaryFile
    {
        if ($filePath instanceof UploadedFile) {
            $readStream = fopen($filePath->getRealPath(), 'rb');
        } elseif ($disk === null && realpath($filePath) !== false) {
            $readStream = fopen($filePath, 'rb');
        } else {
            $diskInstance = app('filesystem')->disk($disk);

            if (!$diskInstance->exists($filePath)) {
                $logPath = '[' . $filePath . ']';

                if ($disk) {
                    $logPath .= ' (' . $disk . ')';
                }

                throw new FileNotFoundException('File ' . $logPath . ' does not exist and can therefore not be imported.');
            }

            $readStream = $diskInstance->readStream($filePath);
        }

        $this->put($readStream);

        if (is_resource($readStream)) {
            fclose($readStream);
        }

        return $this->sync();
    }
}
