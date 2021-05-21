<?php

namespace Maatwebsite\Excel\Columns;

use Illuminate\Support\Arr;
use PhpOffice\PhpSpreadsheet\Cell\Cell;
use PhpOffice\PhpSpreadsheet\Worksheet\Worksheet;

trait Writeable
{
    /**
     * @var callable[]
     */
    protected $writingCallback = [];

    public function beforeWriting(Worksheet $worksheet): void
    {
        $this->formatColumn($worksheet);
        $this->writeStyles($worksheet);
    }

    /**
     * @param mixed $data
     */
    public function write(Worksheet $sheet, int $row, $data): Cell
    {
        $cell = $sheet->getCellByColumnAndRow($this->index, $row);

        if ($this->type) {
            $cell->setDataType($this->type);
        }

        $value = $this->resolveValue($data);

        $this->writeValue($cell, $value);
        $this->writeCellStyle($cell, $data);

        return $cell;
    }

    /**
     * @return $this
     */
    public function writing(callable $writingCallback)
    {
        $this->writingCallback[] = $writingCallback;

        return $this;
    }

    public function afterWriting(Worksheet $worksheet): void
    {
        $this->writeSize($worksheet);
        $this->writeFilters($worksheet);
    }

    protected function writeValue(Cell $cell, $value): void
    {
        $this->type
            ? $cell->setValueExplicit($value, $this->type)
            : $cell->setValue($value);

        foreach ($this->writingCallback as $callback) {
            if (is_callable($callback)) {
                $callback($cell);
            }
        }
    }

    /**
     * @param mixed $data
     *
     * @return mixed
     */
    protected function resolveValue($data)
    {
        if (is_callable($this->attribute)) {
            return ($this->attribute)($data);
        }

        return $this->toExcelValue(
            Arr::get($data, $this->attribute)
        );
    }

    /**
     * @param mixed $value
     *
     * @return mixed
     */
    protected function toExcelValue($value)
    {
        return $value;
    }
}
