<?php

namespace Maatwebsite\Excel\Jobs;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Maatwebsite\Excel\Concerns\Exportable;
use Maatwebsite\Excel\Excel;

class QueueExport implements ShouldQueue
{
    use Queueable;
    use Dispatchable;
    use ProxyFailures;
    use InteractsWithQueue;

    /**
     * @var Exportable
     */
    public $export;

    /**
     * @var string
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
     * @param Exportable  $export
     * @param string      $filePath
     * @param string|null $disk
     * @param string|null $writerType
     * @param array       $diskOptions
     */
    public function __construct($export, string $filePath, ?string $disk = null, ?string $writerType = null, $diskOptions = [])
    {
        $this->export      = $export;
        $this->filePath    = $filePath;
        $this->disk        = $disk;
        $this->writerType  = $writerType;
        $this->diskOptions = $diskOptions;
    }

    public function handle(Excel $excel)
    {
        $excel->store($this->export, $this->filePath, $this->disk, $this->writerType, $this->diskOptions, false);
    }

    /**
     * Get the middleware the job should be dispatched through.
     *
     * @return array
     */
    public function middleware()
    {
        return (method_exists($this->export, 'middleware')) ? $this->export->middleware() : [];
    }
}
