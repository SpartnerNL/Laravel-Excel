<?php

namespace Maatwebsite\Excel\Columns;

use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Config;
use Maatwebsite\Excel\Concerns\WithColumns;
use PhpOffice\PhpSpreadsheet\Worksheet\Worksheet;

class ColumnCollection extends Collection
{
    /**
     * @var Column[]
     */
    protected $items;

    /**
     * @param object|WithColumns $concernable
     *
     * @return ColumnCollection
     */
    public static function makeFrom($concernable): ColumnCollection
    {
        if (!$concernable instanceof WithColumns) {
            return new static([]);
        }

        $index   = 0;
        $columns = [];
        foreach ($concernable->columns() as $key => $column) {
            $index++;
            $column = $column->coordinate(
                is_numeric($key) ? $index : $key
            );

            $columns[$column->letter()] = $column;
        }

        return new static($columns);
    }

    public function beforeWriting(Worksheet $worksheet)
    {
        $this->each(function (Column $column) use ($worksheet) {
            $column->beforeWriting($worksheet);
        });
    }

    public function afterWriting(Worksheet $worksheet)
    {
        $this->each(function (Column $column) use ($worksheet) {
            $column->afterWriting($worksheet);
        });
    }

    public function beforeReading()
    {
        if ($this->needsStyleInformation()) {
            Config::set('excel.imports.read_only', false);
        }
    }

    public function headings(): array
    {
        return $this->map(function (Column $column) {
            return $column->title();
        })->toArray();
    }

    public function start(): string
    {
        return $this->first()->letter();
    }

    public function end(): string
    {
        return $this->last()->letter();
    }

    protected function needsStyleInformation(): bool
    {
        return null !== $this->first(fn(Column $column) => $column->needsStyleInformation());
    }
}
