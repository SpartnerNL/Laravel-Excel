<?php

namespace Maatwebsite\Excel\Files;

use Illuminate\Support\Str;
use RuntimeException;

class TemporaryFileFactory
{
    public function __construct(
        private readonly ?string $temporaryPath = null,
        private readonly ?string $temporaryDisk = null,
    ) {
    }

    public function make(?string $fileExtension = null): TemporaryFile
    {
        if ($this->temporaryDisk !== null) {
            return $this->makeRemote($fileExtension);
        }

        return $this->makeLocal(null, $fileExtension);
    }

    public function makeLocal(?string $fileName = null, ?string $fileExtension = null): LocalTemporaryFile
    {
        if (!is_dir($this->temporaryPath) && !@mkdir($concurrentDirectory = $this->temporaryPath, config('excel.temporary_files.local_permissions.dir', 0o777), true) && !is_dir($concurrentDirectory)) {
            throw new RuntimeException(sprintf('Directory "%s" was not created', $concurrentDirectory));
        }

        return new LocalTemporaryFile(
            $this->temporaryPath . DIRECTORY_SEPARATOR . ($fileName ?: $this->generateFilename($fileExtension))
        );
    }

    private function makeRemote(?string $fileExtension = null): RemoteTemporaryFile
    {
        $filename = $this->generateFilename($fileExtension);

        return new RemoteTemporaryFile(
            $this->temporaryDisk,
            config('excel.temporary_files.remote_prefix') . $filename,
            $this->makeLocal($filename)
        );
    }

    private function generateFilename(?string $fileExtension = null): string
    {
        return 'laravel-excel-' . Str::random(32) . ($fileExtension ? '.' . $fileExtension : '');
    }
}
