<?php

namespace Maatwebsite\Excel\Jobs;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Maatwebsite\Excel\Concerns\FromView;
use Maatwebsite\Excel\Files\TemporaryFile;
use Maatwebsite\Excel\Jobs\Middleware\LocalizeJob;
use Maatwebsite\Excel\Writer;

class AppendViewToSheet implements ShouldQueue
{
    use Queueable, Dispatchable;

    /**
     * @var TemporaryFile
     */
    public $temporaryFile;

    /**
     * @var string
     */
    public $writerType;

    /**
     * @var int
     */
    public $sheetIndex;

    /**
     * @var FromView
     */
    public $sheetExport;

    /**
     * @param FromView        $sheetExport
     * @param TemporaryFile $temporaryFile
     * @param string        $writerType
     * @param int           $sheetIndex
     * @param array         $data
     */
    public function __construct(FromView $sheetExport, TemporaryFile $temporaryFile, string $writerType, int $sheetIndex)
    {
        $this->sheetExport   = $sheetExport;
        $this->temporaryFile = $temporaryFile;
        $this->writerType    = $writerType;
        $this->sheetIndex    = $sheetIndex;
    }

    /**
     * Get the middleware the job should be dispatched through.
     *
     * @return array
     */
    public function middleware()
    {
        return (method_exists($this->sheetExport, 'middleware')) ? $this->sheetExport->middleware() : [];
    }

    /**
     * @param Writer $writer
     *
     * @throws \PhpOffice\PhpSpreadsheet\Exception
     * @throws \PhpOffice\PhpSpreadsheet\Reader\Exception
     */
    public function handle(Writer $writer)
    {
        (new LocalizeJob($this->sheetExport))->handle($this, function () use ($writer) {
            $writer = $writer->reopen($this->temporaryFile, $this->writerType);

            $sheet = $writer->getSheetByIndex($this->sheetIndex);

            $sheet->fromView($this->sheetExport, $this->sheetIndex);

            $writer->write($this->sheetExport, $this->temporaryFile, $this->writerType);
        });
    }
}
