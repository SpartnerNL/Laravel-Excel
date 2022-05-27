<?php

namespace Maatwebsite\Excel\Jobs;

use Illuminate\Bus\Batchable;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Maatwebsite\Excel\Concerns\WithMultipleSheets;
use Maatwebsite\Excel\Files\TemporaryFile;
use Maatwebsite\Excel\Jobs\Middleware\LocalizeJob;
use Maatwebsite\Excel\Writer;
use Throwable;

class QueueExport implements ShouldQueue
{
    use Dispatchable, Batchable, Queueable, InteractsWithQueue;

    /**
     * @var object
     */
    public $export;

    /**
     * @var string
     */
    private $writerType;

    /**
     * @var TemporaryFile
     */
    private $temporaryFile;

    private $jobs_for_batch;

    /**
     * @param  object  $export
     * @param  TemporaryFile  $temporaryFile
     * @param  string  $writerType
     */
    public function __construct($export, TemporaryFile $temporaryFile, string $writerType, $jobs_for_batch = array())
    {
        $this->export        = $export;
        $this->writerType    = $writerType;
        $this->temporaryFile = $temporaryFile;
        $this->jobs_for_batch = $jobs_for_batch;
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

    /**
     * @param  Writer  $writer
     *
     * @throws \PhpOffice\PhpSpreadsheet\Exception
     */
    public function handle(Writer $writer)
    {

        //dump($this->batch()->id);


        if(!empty($this->batch())) {

            if ($this->batch()->cancelled()) {
                // Determine if the batch has been cancelled...

                return;
            }

//            foreach ($this->jobs_for_batch as $jobs_for_batch) {
//
//                $this->batch()->add($jobs_for_batch);
//
//            }

            //dump($this->jobs_for_batch);

            $this->batch()->add($this->jobs_for_batch);


        }

        (new LocalizeJob($this->export))->handle($this, function () use ($writer) {
            $writer->open($this->export);

            $sheetExports = [$this->export];
            if ($this->export instanceof WithMultipleSheets) {
                $sheetExports = $this->export->sheets();
            }

            // Pre-create the worksheets
            foreach ($sheetExports as $sheetIndex => $sheetExport) {
                $sheet = $writer->addNewSheet($sheetIndex);
                $sheet->open($sheetExport);
            }

            // Write to temp file with empty sheets.
            $writer->write($sheetExport, $this->temporaryFile, $this->writerType);
        });
    }

    /**
     * @param  Throwable  $e
     */
    public function failed(Throwable $e)
    {
        if (method_exists($this->export, 'failed')) {
            $this->export->failed($e);
        }
    }
}
