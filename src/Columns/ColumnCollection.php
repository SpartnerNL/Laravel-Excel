<?php

namespace Maatwebsite\Excel\Columns;

use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Config;
use Maatwebsite\Excel\Concerns\WithColumns;
use Maatwebsite\Excel\Concerns\WithHeadingRow;
use PhpOffice\PhpSpreadsheet\Cell\Coordinate;
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
    public static function makeFrom($concernable, ?array $headingRow = null): ColumnCollection
    {
        if (!$concernable instanceof WithColumns) {
            return new static([]);
        }

        $headingMap = is_array($headingRow) ? array_flip($headingRow) : [];
        $headingMap = array_map(fn (int $index) => Coordinate::stringFromColumnIndex($index + 1), $headingMap);

        $index   = 0;
        $columns = [];
        foreach ($concernable->columns() as $key => $column) {
            if (is_array($column)) {
                $column = Column::multiple(...$column);
            }

            $index++;
            $coordinate = is_numeric($key) ? $index : $key;

            if ($concernable instanceof WithHeadingRow) {
                if (!isset($headingMap[$key])) {
                    $column = EmptyCell::make($column->title());
                }

                $coordinate = $headingMap[$key] ?? 99 - $index;
            }

            $column = $column->coordinate($coordinate);

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

    public function start(): ?string
    {
        return $this->sortBy(fn (Column $column) => $column->getIndex())->first()->letter();
    }

    public function end(): ?string
    {
        return $this->sortByDesc(fn (Column $column) => $column->getIndex())->first()->letter();
    }

    protected function needsStyleInformation(): bool
    {
        return null !== $this->first(fn (Column $column) => $column->needsStyleInformation());
    }
}
