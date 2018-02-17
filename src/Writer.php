<?php

namespace Maatwebsite\Excel;

use LogicException;
use PhpOffice\PhpSpreadsheet\IOFactory;
use Maatwebsite\Excel\Concerns\FromView;
use Maatwebsite\Excel\Concerns\FromQuery;
use Maatwebsite\Excel\Concerns\WithTitle;
use PhpOffice\PhpSpreadsheet\Reader\Html;
use PhpOffice\PhpSpreadsheet\Spreadsheet;
use Illuminate\Contracts\Support\Arrayable;
use Maatwebsite\Excel\Concerns\WithMapping;
use Maatwebsite\Excel\Concerns\WithHeadings;
use PhpOffice\PhpSpreadsheet\Worksheet\Worksheet;
use Maatwebsite\Excel\Concerns\InteractsWithSheet;
use Maatwebsite\Excel\Concerns\WithMultipleSheets;
use Maatwebsite\Excel\Concerns\InteractsWithExport;

class Writer
{
    /**
     * @var Spreadsheet
     */
    protected $spreadsheet;

    /**
     * @var object
     */
    protected $export;

    /**
     * @var int
     */
    protected $chunkSize = 100;

    /**
     * @var bool
     */
    protected $hasAppended = false;

    /**
     * @param object $export
     * @param string $writerType
     *
     * @return string
     */
    public function export(object $export, string $writerType): string
    {
        $this->spreadsheet = new Spreadsheet;
        $this->spreadsheet->disconnectWorksheets();

        if ($export instanceof WithTitle) {
            $this->spreadsheet->getProperties()->setTitle($export->title());
        }

        $sheets = [$export];
        if ($export instanceof WithMultipleSheets) {
            $sheets = $export->sheets();
        }

        foreach ($sheets as $sheet) {
            $this->addSheet($sheet);
        }

        if ($export instanceof InteractsWithExport) {
            $export->interact($this->spreadsheet);
        }

        return $this->write($writerType);
    }

    /**
     * @param object $sheet
     */
    protected function addSheet(object $sheet)
    {
        $this->hasAppended = false;
        
        $worksheet = $this->spreadsheet->createSheet();

        if ($sheet instanceof WithTitle) {
            $worksheet->setTitle($sheet->title());
        }

        if ($sheet instanceof FromQuery && $sheet instanceof FromView) {
            throw new LogicException('Cannot use FromQuery and FromView on the same sheet');
        }

        if ($sheet instanceof FromView) {
            $this->fromView($sheet);
        } else {
            if ($sheet instanceof WithHeadings) {
                $this->append($worksheet, [$sheet->headings()]);
            }

            if ($sheet instanceof FromQuery) {
                $this->fromQuery($sheet, $worksheet);
            }
        }

        if ($sheet instanceof InteractsWithSheet) {
            $sheet->interactWithSheet($worksheet);
        }
    }

    /**
     * @param object $sheet
     */
    protected function fromView(object $sheet): void
    {
        $tempFile = $this->tempFile();
        file_put_contents($tempFile, $sheet->view()->render());

        /** @var Html $reader */
        $reader = IOFactory::createReader('Html');
        $reader->setSheetIndex($this->spreadsheet->getActiveSheetIndex());
        $this->spreadsheet = $reader->loadIntoExisting($tempFile, $this->spreadsheet);
    }

    /**
     * @param object    $sheet
     * @param Worksheet $worksheet
     */
    protected function fromQuery(object $sheet, Worksheet $worksheet): void
    {
        $sheet->query()->chunk($this->chunkSize, function ($chunk) use ($sheet, $worksheet) {
            foreach ($chunk as $row) {
                if ($sheet instanceof WithMapping) {
                    $row = $sheet->map($row);
                }

                if ($row instanceof Arrayable) {
                    $row = $row->toArray();
                }

                $this->append($worksheet, [$row]);
            }
        });
    }

    /**
     * @param Worksheet $worksheet
     * @param array     $rows
     */
    protected function append(Worksheet $worksheet, array $rows)
    {
        $row = $worksheet->getHighestRow();

        if ($this->hasAppended) {
            $row++;
        }

        $worksheet->fromArray($rows, null, 'A' . $row);
        
        $this->hasAppended = true;
    }

    /**
     * @param string $writerType
     *
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
