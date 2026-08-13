<?php

declare(strict_types=1);

namespace Maatwebsite\Excel\Columns;

use Closure;
use Illuminate\Support\Arr;
use PhpOffice\PhpSpreadsheet\Cell\Cell;
use PhpOffice\PhpSpreadsheet\Cell\DataType;
use PhpOffice\PhpSpreadsheet\Exception;
use PhpOffice\PhpSpreadsheet\Worksheet\Worksheet;

trait Writeable
{
    /**
     * @var list<Closure>
     */
    protected array $writingCallbacks = [];

    /**
     * @throws Exception
     */
    public function beforeWriting(Worksheet $worksheet): void
    {
        $this->formatColumn($worksheet);
        $this->writeStyles($worksheet);
    }

    /**
     * @throws Exception
     */
    public function write(Worksheet $sheet, int $row, mixed $data): Cell
    {
        $cell = $sheet->getCell([$this->index, $row]);

        if ($this->type !== null) {
            $cell->setDataType($this->type);
        }

        $value = $this->resolveValue($data);

        $this->writeValue($sheet, $cell, $value);
        $this->writeCellStyle($cell, $data);

        return $cell;
    }

    public function writing(Closure $writingCallback): static
    {
        $this->writingCallbacks[] = $writingCallback;

        return $this;
    }

    /**
     * @throws Exception
     */
    public function afterWriting(Worksheet $worksheet): void
    {
        $this->writeColumnDimensions($worksheet);
        $this->writeFilters($worksheet);
    }

    /**
     * @throws Exception
     */
    protected function writeValue(Worksheet $worksheet, Cell $cell, mixed $value): void
    {
        if ($value === null && $this->nullable) {
            $cell->setValueExplicit($value, DataType::TYPE_NULL);
        } elseif ($this->type !== null) {
            $cell->setValueExplicit($value, $this->type);
        } else {
            $cell->setValue($value);
        }

        foreach ($this->writingCallbacks as $callback) {
            $callback($cell);
        }
    }

    protected function resolveValue(mixed $data): mixed
    {
        if ($this->attribute instanceof Closure) {
            return ($this->attribute)($data);
        }

        $value = Arr::get($data, $this->attribute);

        if ($this->nullable && $value === null) {
            return null;
        }

        return $this->toExcelValue($value);
    }

    protected function toExcelValue(mixed $value): mixed
    {
        return $value;
    }
}
