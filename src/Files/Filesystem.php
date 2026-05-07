<?php

namespace Maatwebsite\Excel\Files;

use Illuminate\Contracts\Filesystem\Factory;

class Filesystem
{
    public function __construct(private Factory $filesystem)
    {
    }

    public function disk(?string $disk = null, array $diskOptions = []): Disk
    {
        return new Disk(
            $this->filesystem->disk($disk),
            $disk,
            $diskOptions
        );
    }
}
