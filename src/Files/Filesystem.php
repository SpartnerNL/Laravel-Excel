<?php

namespace Maatwebsite\Excel\Files;

use Illuminate\Contracts\Filesystem\Factory;

class Filesystem
{
    /**
     * @param  Factory  $filesystem
     */
    public function __construct(private Factory $filesystem)
    {
    }

    /**
     * @param  string|null  $disk
     * @param  array  $diskOptions
     * @return Disk
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
