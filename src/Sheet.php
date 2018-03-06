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
use Maatwebsite\Excel\Concerns\WithEvents;
use Illuminate\Contracts\Support\Arrayable;
use Maatwebsite\Excel\Concerns\WithMapping;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\FromCollection;
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
    protected $chunkSize;

    /**
     * @var string
     */
    protected $tmpPath;

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
        $this->chunkSize = config('excel.exports.chunk_size', 100);
        $this->tmpPath   = config('excel.exports.temp_path', sys_get_temp_dir());
    }

    /**
     * @param object $sheetExport
     *
     * @throws \PhpOffice\PhpSpreadsheet\Exception
     */
    public function open($sheetExport)
    {
        if ($sheetExport instanceof WithEvents) {
            $this->registerListeners($sheetExport->registerEvents());
        }

        $this->raise(new BeforeSheet($this));

        if ($sheetExport instanceof WithTitle) {
            $this->worksheet->setTitle($sheetExport->title());
        }

        if (($sheetExport instanceof FromQuery || $sheetExport instanceof FromCollection) && $sheetExport instanceof FromView) {
            throw new LogicException('Cannot use FromQuery and FromView on the same sheet');
        }

        if (!$sheetExport instanceof FromView && $sheetExport instanceof WithHeadings) {
            $this->append([$sheetExport->headings()]);
        }
    }

    /**
     * @param object $sheetExport
     *
     * @throws \PhpOffice\PhpSpreadsheet\Exception
     * @throws \PhpOffice\PhpSpreadsheet\Reader\Exception
     */
    public function export($sheetExport)
    {
        $this->open($sheetExport);

        if ($sheetExport instanceof FromView) {
            $this->fromView($sheetExport);
        } else {
            if ($sheetExport instanceof FromQuery) {
                $this->fromQuery($sheetExport, $this->worksheet);
            }

            if ($sheetExport instanceof FromCollection) {
                $this->fromCollection($sheetExport, $this->worksheet);
            }
        }

        $this->close($sheetExport);
    }

    /**
     * @param object $sheetExport
     *
     * @throws \PhpOffice\PhpSpreadsheet\Exception
     */
    public function close($sheetExport)
    {
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
     * @param FromView $sheetExport
     *
     * @throws \PhpOffice\PhpSpreadsheet\Reader\Exception
     */
    public function fromView(FromView $sheetExport): void
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
     * @param FromQuery $sheetExport
     * @param Worksheet $worksheet
     */
    public function fromQuery(FromQuery $sheetExport, Worksheet $worksheet): void
    {
        $sheetExport->query()->chunk($this->chunkSize, function ($chunk) use ($sheetExport, $worksheet) {
            foreach ($chunk as $row) {
                $this->appendRow($row, $sheetExport);
            }
        });
    }

    /**
     * @param FromCollection $sheetExport
     * @param Worksheet      $worksheet
     */
    public function fromCollection(FromCollection $sheetExport, Worksheet $worksheet)
    {
        $sheetExport
            ->collection()
            ->each(function ($row) use ($sheetExport, $worksheet) {
                $this->appendRow($row, $sheetExport);
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
     * @param int $chunkSize
     *
     * @return Sheet
     */
    public function chunkSize(int $chunkSize)
    {
        $this->chunkSize = $chunkSize;

        return $this;
    }

    /**
     * @return Worksheet
     */
    public function getDelegate()
    {
        return $this->worksheet;
    }

    /**
     * @param iterable $row
     * @param object   $sheetExport
     *
     * @throws \PhpOffice\PhpSpreadsheet\Exception
     */
    protected function appendRow($row, $sheetExport)
    {
        if ($sheetExport instanceof WithMapping) {
            $row = $sheetExport->map($row);
        }

        if ($row instanceof Arrayable) {
            $row = $row->toArray();
        }

        $this->append([$row]);
    }

    /**
     * @param iterable $rows
     * @param object   $sheetExport
     *
     * @throws \PhpOffice\PhpSpreadsheet\Exception
     */
    public function appendRows($rows, $sheetExport)
    {
        $append = [];
        foreach ($rows as $row) {
            if ($sheetExport instanceof WithMapping) {
                $row = $sheetExport->map($row);
            }

            if ($row instanceof Arrayable) {
                $row = $row->toArray();
            }

            $append[] = $row;
        }

        $this->append($append);
    }

    /**
     * @return string
     */
    protected function tempFile(): string
    {
        return tempnam($this->tmpPath, 'laravel-excel');
    }
}
