<?php

namespace Maatwebsite\Excel\Jobs;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Maatwebsite\Excel\Helpers\FilePathHelper;

class StoreQueuedExport implements ShouldQueue
{
    use Queueable;

    /**
     * @var string
     */
    private $tempFile;

    /**
     * @var string
     */
    private $path;

    /**
     * @var string|null
     */
    private $disk;

    /**
     * @var mixed
     */
    private $diskOptions;

    /**
     * @param string      $tempFile
     * @param string      $path
     * @param string|null $disk
     * @param mixed       $diskOptions
     */
    public function __construct(string $tempFile, string $path, string $disk = null, $diskOptions = [])
    {
        $this->tempFile    = $tempFile;
        $this->path        = $path;
        $this->disk        = $disk;
        $this->diskOptions = $diskOptions;
    }

    /**
     * @param FilePathHelper $filePathHelper
     */
    public function handle(FilePathHelper $filePathHelper)
    {
        $filePathHelper->storeToDisk(
            $filePathHelper->getTempPath($this->tempFile),
            $this->path,
            $this->disk,
            $this->diskOptions
        );
    }
}
