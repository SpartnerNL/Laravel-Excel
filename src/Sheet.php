<?php

namespace Maatwebsite\Excel;

use LogicException;
use PhpOffice\PhpSpreadsheet\IOFactory;
use Maatwebsite\Excel\Concerns\FromView;
use Maatwebsite\Excel\Events\AfterSheet;
use Maatwebsite\Excel\Concerns\FromQuery;
use Maatwebsite\Excel\Concerns\WithTitle;
use Maatwebsite\Excel\Events\BeforeSheet;
use PhpOffice\PhpSpreadsheet\Reader\Html;
use Illuminate\Contracts\Support\Arrayable;
use Maatwebsite\Excel\Concerns\WithMapping;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\ShouldAutoSize;
use PhpOffice\PhpSpreadsheet\Worksheet\Worksheet;
use Maatwebsite\Excel\Concerns\WithColumnFormatting;

class Sheet
{
    use DelegatedMacroable, HasEventBus;

    /**
     * @var bool
     */
    protected $hasAppended = false;

    /**
     * @var int
     */
    protected $chunkSize = 100;

    /**
     * @var Worksheet
     */
    private $worksheet;

    /**
     * @param Worksheet $worksheet
     */
    public function __construct(Worksheet $worksheet)
    {
        $this->worksheet = $worksheet;
    }

    /**
     * @param object $sheetExport
     *
     * @throws \PhpOffice\PhpSpreadsheet\Exception
     * @throws \PhpOffice\PhpSpreadsheet\Reader\Exception
     */
    public function export($sheetExport)
    {
        $this->raise(new BeforeSheet($this));

        if ($sheetExport instanceof WithTitle) {
            $this->worksheet->setTitle($sheetExport->title());
        }

        if ($sheetExport instanceof FromQuery && $sheetExport instanceof FromView) {
            throw new LogicException('Cannot use FromQuery and FromView on the same sheet');
        }

        if ($sheetExport instanceof FromView) {
            $this->fromView($sheetExport);
        } else {
            if ($sheetExport instanceof WithHeadings) {
                $this->append([$sheetExport->headings()]);
            }

            if ($sheetExport instanceof FromQuery) {
                $this->fromQuery($sheetExport, $this->worksheet);
            }
        }

        if ($sheetExport instanceof WithColumnFormatting) {
            foreach ($sheetExport->columnFormats() as $column => $format) {
                $this->formatColumn($column, $format);
            }
        }

        if ($sheetExport instanceof ShouldAutoSize) {
            $this->autoSize();
        }

        $this->raise(new AfterSheet($this));
    }

    /**
     * @param object $sheetExport
     *
     * @throws \PhpOffice\PhpSpreadsheet\Reader\Exception
     */
    public function fromView($sheetExport): void
    {
        $tempFile = $this->tempFile();
        file_put_contents($tempFile, $sheetExport->view()->render());

        $spreadsheet = $this->worksheet->getParent();

        /** @var Html $reader */
        $reader = IOFactory::createReader('Html');
        $reader->setSheetIndex($spreadsheet->getActiveSheetIndex());
        $reader->loadIntoExisting($tempFile, $spreadsheet);
    }

    /**
     * @param object    $sheetExport
     * @param Worksheet $worksheet
     */
    public function fromQuery($sheetExport, Worksheet $worksheet): void
    {
        $sheetExport->query()->chunk($this->chunkSize, function ($chunk) use ($sheetExport, $worksheet) {
            foreach ($chunk as $row) {
                if ($sheetExport instanceof WithMapping) {
                    $row = $sheetExport->map($row);
                }

                if ($row instanceof Arrayable) {
                    $row = $row->toArray();
                }

                $this->append([$row]);
            }
        });
    }

    /**
     * @param array $rows
     *
     * @throws \PhpOffice\PhpSpreadsheet\Exception
     */
    public function append(array $rows)
    {
        $row = $this->worksheet->getHighestRow();

        if ($this->hasAppended) {
            $row++;
        }

        $this->worksheet->fromArray($rows, null, 'A' . $row);

        $this->hasAppended = true;
    }

    /**
     * @return void
     */
    public function autoSize(): void
    {
        foreach (range('A', $this->worksheet->getHighestDataColumn()) as $col) {
            $this->worksheet->getColumnDimension($col)->setAutoSize(true);
        }
    }

    /**
     * @param string $column
     * @param string $format
     *
     * @throws \PhpOffice\PhpSpreadsheet\Exception
     */
    public function formatColumn(string $column, string $format): void
    {
        $this->worksheet
            ->getStyle($column . '1:' . $column . $this->worksheet->getHighestRow())
            ->getNumberFormat()
            ->setFormatCode($format);
    }

    /**
     * @return Worksheet
     */
    public function getDelegate()
    {
        return $this->worksheet;
    }

    /**
     * @return string
     */
    protected function tempFile(): string
    {
        return tempnam(sys_get_temp_dir(), 'laravel-excel');
    }
}
