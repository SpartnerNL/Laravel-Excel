<?php

namespace Maatwebsite\Excel\Columns;

use Illuminate\Support\Collection;
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
        foreach ($concernable->columns() as $column) {
            $index++;
            $columns[] = $column->index($index);
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

    public function headings(): array
    {
        return $this->map(function (Column $column) {
            return $column->title();
        })->toArray();
    }
}
