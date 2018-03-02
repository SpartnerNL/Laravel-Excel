<?php

namespace Maatwebsite\Excel;

use PhpOffice\PhpSpreadsheet\IOFactory;
use Maatwebsite\Excel\Concerns\WithTitle;
use PhpOffice\PhpSpreadsheet\Spreadsheet;
use Maatwebsite\Excel\Concerns\WithMultipleSheets;
use Maatwebsite\Excel\Concerns\InteractsWithWriter;

class Writer
{
    use DelegatedMacroable;

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
     * @return string
     */
    public function export($export, string $writerType): string
    {
        $this->spreadsheet = new Spreadsheet;
        $this->spreadsheet->disconnectWorksheets();

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

        if ($export instanceof InteractsWithWriter) {
            $export->interact($this);
        }

        return $this->write($writerType);
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
        $worksheet = $this->spreadsheet->createSheet();

        $sheet = new Sheet($worksheet);
        $sheet->export($sheetExport);
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
