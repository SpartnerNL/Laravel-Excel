<?php

namespace Maatwebsite\Excel\Files;

use Illuminate\Contracts\Filesystem\FileNotFoundException;
use Symfony\Component\HttpFoundation\File\UploadedFile;
use Throwable;

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
     * When no disk is given, a string path is a local filesystem path and is not
     * confined to a disk. Never pass unvalidated user input without a disk.
     *
     * @throws FileNotFoundException
     */
    public function copyFrom(string|UploadedFile $filePath, ?string $disk = null): TemporaryFile
    {
        if ($filePath instanceof UploadedFile) {
            $readStream = fopen($filePath->getRealPath(), 'rb');
        } elseif ($disk === null && $this->isLocalFilesystemPath($filePath)) {
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

    /**
     * A path is only read from the local filesystem when no disk was given. Absolute
     * paths are read as-is, but a relative path is resolved against the disk first, so
     * the working directory can never shadow a file that lives on the disk.
     */
    private function isLocalFilesystemPath(string $filePath): bool
    {
        if (realpath($filePath) === false) {
            return false;
        }

        if ($this->isAbsolutePath($filePath)) {
            return true;
        }

        try {
            return !app('filesystem')->disk()->exists($filePath);
        } catch (Throwable) {
            // The disk cannot resolve the path at all, so fall back to the local filesystem.
            return true;
        }
    }

    private function isAbsolutePath(string $filePath): bool
    {
        if ($filePath === '') {
            return false;
        }

        if ($filePath[0] === '/' || $filePath[0] === '\\') {
            return true;
        }

        // Windows drive letters, e.g. C:\path or C:/path.
        return strlen($filePath) > 2
            && ctype_alpha($filePath[0])
            && $filePath[1] === ':'
            && ($filePath[2] === '/' || $filePath[2] === '\\');
    }
}
