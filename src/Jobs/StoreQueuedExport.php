<?php

namespace Maatwebsite\Excel\Jobs;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Filesystem\FilesystemManager;

class StoreQueuedExport implements ShouldQueue
{
    use Queueable;

    /**
     * @var string
     */
    private $tempPath;

    /**
     * @var string
     */
    private $path;

    /**
     * @var string|null
     */
    private $disk;

    /**
     * @param string      $tempPath
     * @param string      $path
     * @param string|null $disk
     */
    public function __construct(string $tempPath, string $path, string $disk = null)
    {
        $this->tempPath = $tempPath;
        $this->path     = $path;
        $this->disk     = $disk;
    }

    /**
     * @param FilesystemManager $filesystem
     */
    public function handle(FilesystemManager $filesystem)
    {
        $filesystem->disk($this->disk)->put($this->path, fopen($this->tempPath, 'r+'));
    }
}
