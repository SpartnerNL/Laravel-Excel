<?php

namespace Maatwebsite\Excel\Jobs;

use Illuminate\Bus\Queueable;
use Maatwebsite\Excel\Writer;
use PhpOffice\PhpSpreadsheet\IOFactory;
use Maatwebsite\Excel\Concerns\FromView;
use PhpOffice\PhpSpreadsheet\Reader\Html;
use Maatwebsite\Excel\Files\TemporaryFile;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Maatwebsite\Excel\Files\TemporaryFileFactory;

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
     * @var TemporaryFileFactory
     */
    protected $temporaryFileFactory;

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
        $this->temporaryFileFactory = app(TemporaryFileFactory::class);
    }

    /**
     * @param Writer $writer
     *
     * @throws \PhpOffice\PhpSpreadsheet\Exception
     * @throws \PhpOffice\PhpSpreadsheet\Reader\Exception
     */
    public function handle(Writer $writer)
    {
        $writer = $writer->reopen($this->temporaryFile, $this->writerType);

        $temporaryFile = $this->temporaryFileFactory->makeLocal();
        $temporaryFile->put($this->sheetExport->view()->render());
        /** @var Html $reader */
        $reader = IOFactory::createReader('Html');

        // Insert content into the last sheet
        $reader->setSheetIndex($this->sheetIndex);
        $reader->loadIntoExisting($temporaryFile->getLocalPath(), $writer->getDelegate());
        $temporaryFile->delete();

        $writer->write($this->sheetExport, $this->temporaryFile, $this->writerType);
    }
}
