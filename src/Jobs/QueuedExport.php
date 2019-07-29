<?php

namespace Maatwebsite\Excel\Jobs;

use Throwable;
use Illuminate\Bus\Queueable;
use Maatwebsite\Excel\Writer;
use Maatwebsite\Excel\Files\Disk;
use Illuminate\Queue\SerializesModels;
use Illuminate\Queue\InteractsWithQueue;
use Maatwebsite\Excel\Helpers\StoreHelper;
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
     * @var Writer
     */
    public $writer;

    /**
     * @var Disk
     */
    public $disk;

    /**
     * @var array
     */
    public $filePath;

    /**
     * @var string|null
     */
    public $writerType;

    /**
     * @var int
     */
    public $tries;

    /**
     * Create a new job instance.
     *
     * @param object $export
     * @param Writer $writer
     * @param Disk $disk
     * @param string $filePath
     * @param string|null $writerType
     * @param int|null $tries
     */
    public function __construct($export, Writer $writer, Disk $disk, string $filePath, string $writerType = null, int $tries = null)
    {
        $this->export      = $export;
        $this->writer      = $writer;
        $this->disk        = $disk;
        $this->filePath    = $filePath;
        $this->writerType  = $writerType;
        $this->tries       = $tries;
    }

    /**
     * Execute the job.
     *
     * @throws \Maatwebsite\Excel\Exceptions\NoTypeDetectedException
     * @throws \PhpOffice\PhpSpreadsheet\Exception
     * @return void
     */
    public function handle()
    {
        StoreHelper::store($this->export, $this->writer, $this->disk, $this->filePath, $this->writerType);
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
