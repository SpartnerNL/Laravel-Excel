<?php

namespace Maatwebsite\Excel\Jobs;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Maatwebsite\Excel\Files\Disk;
use Maatwebsite\Excel\Files\TemporaryFile;
use Maatwebsite\Excel\Helpers\FilePathHelper;

class StoreQueuedExport implements ShouldQueue
{
    use Queueable;

    /**
     * @var string
     */
    private $filePath;

    /**
     * @var Disk
     */
    private $disk;

    /**
     * @var TemporaryFile
     */
    private $temporaryFile;

    /**
     * @param TemporaryFile $temporaryFile
     * @param Disk          $disk
     * @param string        $filePath
     */
    public function __construct(TemporaryFile $temporaryFile, Disk $disk, string $filePath)
    {
        $this->disk          = $disk;
        $this->filePath      = $filePath;
        $this->temporaryFile = $temporaryFile;
    }

    public function handle()
    {
        $this->disk->put(
            $this->temporaryFile->getLocalPath(),
            $this->filePath
        );
    }
}
