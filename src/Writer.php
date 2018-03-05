<?php

namespace Maatwebsite\Excel;

use Maatwebsite\Excel\Concerns\WithEvents;
use Maatwebsite\Excel\Events\BeforeExport;
use Maatwebsite\Excel\Events\BeforeWriting;
use PhpOffice\PhpSpreadsheet\IOFactory;
use Maatwebsite\Excel\Concerns\WithTitle;
use PhpOffice\PhpSpreadsheet\Spreadsheet;
use Maatwebsite\Excel\Concerns\WithMultipleSheets;

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
    protected $export;

    /**
     * @param object $export
     * @param string $writerType
     *
     * @throws \PhpOffice\PhpSpreadsheet\Exception
     * @throws \PhpOffice\PhpSpreadsheet\Writer\Exception
     * @return string
     */
    public function export($export, string $writerType): string
    {
        if ($export instanceof WithEvents) {
            $this->registerListeners($export->registerEvents());
        }

        $this->spreadsheet = new Spreadsheet;
        $this->spreadsheet->disconnectWorksheets();

        $this->raise(new BeforeExport($this));

        if ($export instanceof WithTitle) {
            $this->spreadsheet->getProperties()->setTitle($export->title());
        }

        $sheetExports = [$export];
        if ($export instanceof WithMultipleSheets) {
            $sheetExports = $export->sheets();
        }

        foreach ($sheetExports as $sheetExportExport) {
            $this->addSheet($sheetExportExport);
        }

        $this->raise(new BeforeWriting($this));

        return $this->write($writerType);
    }

    /**
     * @throws \PhpOffice\PhpSpreadsheet\Exception
     * @return Sheet
     */
    public function addNewSheet()
    {
        return new Sheet($this->spreadsheet->createSheet());
    }

    /**
     * @return Spreadsheet
     */
    public function getDelegate()
    {
        return $this->spreadsheet;
    }

    /**
     * @param object $sheetExport
     *
     * @throws \PhpOffice\PhpSpreadsheet\Exception
     */
    protected function addSheet($sheetExport)
    {
        $this->addNewSheet()->export($sheetExport);
    }

    /**
     * @param string $writerType
     *
     * @throws \PhpOffice\PhpSpreadsheet\Writer\Exception
     * @return string: string
     */
    protected function write(string $writerType)
    {
        $fileName = $this->tempFile();

        $writer = IOFactory::createWriter($this->spreadsheet, $writerType);
        $writer->save($fileName);

        return $fileName;
    }

    /**
     * @return string
     */
    protected function tempFile(): string
    {
        return tempnam(sys_get_temp_dir(), 'laravel-excel');
    }
}
