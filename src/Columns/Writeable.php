<?php

namespace Maatwebsite\Excel\Columns;

use Closure;
use Illuminate\Support\Arr;
use PhpOffice\PhpSpreadsheet\Cell\Cell;
use PhpOffice\PhpSpreadsheet\Cell\DataType;
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
     * @param  mixed  $data
     */
    public function write(Worksheet $sheet, int $row, $data): Cell
    {
        $cell = $sheet->getCellByColumnAndRow($this->index, $row);

        if ($this->type) {
            $cell->setDataType($this->type);
        }

        $value = $this->resolveValue($data);

        $this->writeValue($sheet, $cell, $value);
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
        $this->writeColumnDimensions($worksheet);
        $this->writeFilters($worksheet);
    }

    protected function writeValue(Worksheet $worksheet, Cell $cell, $value): void
    {
        if (null === $value && $this->nullable) {
            $cell->setValueExplicit($value, DataType::TYPE_NULL);
        } else {
            $this->type
                ? $cell->setValueExplicit($value, $this->type)
                : $cell->setValue($value);
        }

        foreach ($this->writingCallback as $callback) {
            if (is_callable($callback)) {
                $callback($cell);
            }
        }
    }

    /**
     * @param  mixed  $data
     * @return mixed
     */
    protected function resolveValue($data)
    {
        if ($this->attribute instanceof Closure) {
            return ($this->attribute)($data);
        }

        $value = Arr::get($data, $this->attribute);

        if ($this->nullable && null === $value) {
            return null;
        }

        return $this->toExcelValue($value);
    }

    /**
     * @param  mixed  $value
     * @return mixed
     */
    protected function toExcelValue($value)
    {
        return $value;
    }
}
