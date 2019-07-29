<?php

namespace Maatwebsite\Excel\Jobs;

use Throwable;
use Illuminate\Bus\Queueable;
use Illuminate\Queue\SerializesModels;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;

class QueuedExport implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    /**
     * @var object
     */
    public $export;

    /**
     * @var array
     */
    public $filePath;

    /**
     * @var string|null
     */
    public $disk;

    /**
     * @var string|null
     */
    public $writerType;

    /**
     * @var array
     */
    public $diskOptions;

    /**
     * @var int
     */
    public $tries;

    /**
     * Create a new job instance.
     *
     * @param object $export
     * @param string $filePath
     * @param string|null $disk
     * @param string|null $writerType
     * @param array $diskOptions
     * @param int|null $tries
     */
    public function __construct($export, string $filePath, string $disk = null, string $writerType = null, $diskOptions = [], $tries = null)
    {
        $this->export      = $export;
        $this->filePath    = $filePath;
        $this->disk        = $disk;
        $this->writerType  = $writerType;
        $this->diskOptions = $diskOptions;
        $this->tries       = $tries;
    }

    /**
     * Execute the job.
     *
     * @return void
     */
    public function handle()
    {
        $this->export->getExporter()->storeQueued(
            $this->export,
            $this->filePath,
            $this->disk,
            $this->writerType,
            $this->diskOptions
        );
    }

    /**
     * @param Throwable $e
     */
    public function failed(Throwable $e)
    {
        if (method_exists($this->export, 'failed')) {
            $this->export->failed($e);
        }
    }
}
