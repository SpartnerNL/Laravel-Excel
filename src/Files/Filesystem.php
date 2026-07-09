<?php

namespace Maatwebsite\Excel\Files;

use Illuminate\Contracts\Filesystem\Factory;

class Filesystem
{
    public function __construct(
        private readonly Factory $filesystem,
    ) {
    }

    /**
     * @param  array<string, mixed>  $diskOptions
     */
    public function disk(?string $disk = null, array $diskOptions = []): Disk
    {
        return new Disk(
            $this->filesystem->disk($disk),
            $disk,
            $diskOptions
        );
    }
}
