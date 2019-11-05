<?php

namespace Maatwebsite\Excel;

use Maatwebsite\Excel\Concerns\WithCustomValueBinder;
use Maatwebsite\Excel\Concerns\WithEvents;
use Maatwebsite\Excel\Concerns\WithMultipleSheets;
use Maatwebsite\Excel\Concerns\WithTitle;
use Maatwebsite\Excel\Events\BeforeExport;
use Maatwebsite\Excel\Events\BeforeWriting;
use Maatwebsite\Excel\Factories\WriterFactory;
use Maatwebsite\Excel\Files\RemoteTemporaryFile;
use Maatwebsite\Excel\Files\TemporaryFile;
use Maatwebsite\Excel\Files\TemporaryFileFactory;
use PhpOffice\PhpSpreadsheet\Cell\Cell;
use PhpOffice\PhpSpreadsheet\IOFactory;
use PhpOffice\PhpSpreadsheet\Spreadsheet;

class Writer
{
    use DelegatedMacroable, HasEventBus;

    /**
     * @var Spreadsheet
     */
    protected $spreadsheet;

    /**
     * @var object
     */
    protected $exportable;

    /**
     * @var TemporaryFileFactory
     */
    protected $temporaryFileFactory;

    /**
     * @param TemporaryFileFactory $temporaryFileFactory
     */
    public function __construct(TemporaryFileFactory $temporaryFileFactory)
    {
        $this->temporaryFileFactory = $temporaryFileFactory;

        $this->setDefaultValueBinder();
    }

    /**
     * @param object $export
     * @param string $writerType
     *
     * @throws \PhpOffice\PhpSpreadsheet\Exception
     * @return TemporaryFile
     */
    public function export($export, string $writerType): TemporaryFile
    {
        $this->open($export);

        $sheetExports = [$export];
        if ($export instanceof WithMultipleSheets) {
            $sheetExports = $export->sheets();
        }

        foreach ($sheetExports as $sheetExport) {
            $this->addNewSheet()->export($sheetExport);
        }

        return $this->write($export, $this->temporaryFileFactory->makeLocal(), $writerType);
    }

    /**
     * @param object $export
     *
     * @return $this
     */
    public function open($export)
    {
        $this->exportable = $export;

        if ($export instanceof WithEvents) {
            $this->registerListeners($export->registerEvents());
        }

        $this->exportable  = $export;
        $this->spreadsheet = new Spreadsheet;
        $this->spreadsheet->disconnectWorksheets();

        if ($export instanceof WithCustomValueBinder) {
            Cell::setValueBinder($export);
        }

        $this->raise(new BeforeExport($this, $this->exportable));

        if ($export instanceof WithTitle) {
            $this->spreadsheet->getProperties()->setTitle($export->title());
        }

        return $this;
    }

    /**
     * @param TemporaryFile $tempFile
     * @param string        $writerType
     *
     * @throws \PhpOffice\PhpSpreadsheet\Reader\Exception
     * @return Writer
     */
    public function reopen(TemporaryFile $tempFile, string $writerType)
    {
        $reader            = IOFactory::createReader($writerType);
        $this->spreadsheet = $reader->load($tempFile->sync()->getLocalPath());

        return $this;
    }

    /**
     * @param object        $export
     * @param TemporaryFile $temporaryFile
     * @param string        $writerType
     *
     * @throws \PhpOffice\PhpSpreadsheet\Exception
     * @throws \PhpOffice\PhpSpreadsheet\Writer\Exception
     * @return TemporaryFile
     */
    public function write($export, TemporaryFile $temporaryFile, string $writerType): TemporaryFile
    {
        $this->exportable = $export;

        $this->spreadsheet->setActiveSheetIndex(0);

        $this->raise(new BeforeWriting($this, $this->exportable));

        $writer = WriterFactory::make(
            $writerType,
            $this->spreadsheet,
            $export
        );

        $writer->save(
            $path = $temporaryFile->getLocalPath()
        );

        if ($temporaryFile instanceof RemoteTemporaryFile) {
            $temporaryFile->updateRemote();
        }

        $this->spreadsheet->disconnectWorksheets();
        unset($this->spreadsheet);

        return $temporaryFile;
    }

    /**
     * @param int|null $sheetIndex
     *
     * @throws \PhpOffice\PhpSpreadsheet\Exception
     * @return Sheet
     */
    public function addNewSheet(int $sheetIndex = null)
    {
        return new Sheet($this->spreadsheet->createSheet($sheetIndex));
    }

    /**
     * @return Spreadsheet
     */
    public function getDelegate()
    {
        return $this->spreadsheet;
    }

    /**
     * @return $this
     */
    public function setDefaultValueBinder()
    {
        Cell::setValueBinder(
            app(config('excel.value_binder.default', DefaultValueBinder::class))
        );

        return $this;
    }

    /**
     * @param int $sheetIndex
     *
     * @throws \PhpOffice\PhpSpreadsheet\Exception
     * @return Sheet
     */
    public function getSheetByIndex(int $sheetIndex)
    {
        return new Sheet($this->getDelegate()->getSheet($sheetIndex));
    }

    /**
     * @param string $concern
     *
     * @return bool
     */
    public function hasConcern($concern): bool
    {
        return $this->exportable instanceof $concern;
    }
}
