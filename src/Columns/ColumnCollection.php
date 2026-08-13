<?php

declare(strict_types=1);

namespace Maatwebsite\Excel\Columns;

use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Config;
use Maatwebsite\Excel\Concerns\WithColumns;
use Maatwebsite\Excel\Concerns\WithHeadingRow;
use PhpOffice\PhpSpreadsheet\Cell\Coordinate;
use PhpOffice\PhpSpreadsheet\Exception;
use PhpOffice\PhpSpreadsheet\Worksheet\Worksheet;

/**
 * @extends Collection<string, Column>
 */
class ColumnCollection extends Collection
{
    /**
     * @param  list<string>|null  $headingRow
     *
     * @throws Exception
     */
    public static function makeFrom(object $concernable, ?array $headingRow = null): self
    {
        if (!$concernable instanceof WithColumns) {
            return new self([]);
        }

        $headingMap = is_array($headingRow) ? array_flip($headingRow) : [];
        $headingMap = array_map(fn (int $index): string => Coordinate::stringFromColumnIndex($index + 1), $headingMap);

        $index   = 0;
        $columns = [];
        foreach ($concernable->columns() as $key => $column) {
            if (is_array($column)) {
                $column = Column::multiple(...$column);
            }

            $index++;
            $coordinate = is_int($key) ? $index : $key;

            if ($concernable instanceof WithHeadingRow) {
                if (!isset($headingMap[$key])) {
                    $column = EmptyCell::make($column->title());
                }

                // Columns without a matching heading are pushed beyond the
                // last real column so they never collide with one.
                $coordinate = $headingMap[$key] ?? 99 - $index;
            }

            $column = $column->coordinate($coordinate);

            $columns[$column->letter()] = $column;
        }

        return new self($columns);
    }

    /**
     * @throws Exception
     */
    public function beforeWriting(Worksheet $worksheet): void
    {
        $this->each(function (Column $column) use ($worksheet): void {
            $column->beforeWriting($worksheet);
        });
    }

    /**
     * @throws Exception
     */
    public function afterWriting(Worksheet $worksheet): void
    {
        $this->sortByColumn()->filter(fn (Column $column): bool => $column->hasAutoFilter())->pipe(function (self $columns) use ($worksheet): void {
            if ($columns->isEmpty()) {
                return;
            }

            $start = $columns->first();
            $end   = $columns->last();

            $worksheet->setAutoFilter(
                $start->letter() . '1:' . $end->letter() . $worksheet->getHighestRow()
            );
        });

        $this->each(function (Column $column) use ($worksheet): void {
            $column->afterWriting($worksheet);
        });
    }

    public function beforeReading(): void
    {
        if ($this->needsStyleInformation()) {
            Config::set('excel.imports.read_only', false);
        }
    }

    /**
     * @return array<string, string>
     */
    public function headings(): array
    {
        $headings = [];

        foreach ($this as $letter => $column) {
            $headings[$letter] = $column->title();
        }

        return $headings;
    }

    public function start(): ?string
    {
        return $this->sortByColumn()->first()?->letter();
    }

    public function end(): ?string
    {
        return $this->sortByColumn()->last()?->letter();
    }

    public function sortByColumn(): self
    {
        return $this->sortBy(fn (Column $column): int => $column->getIndex());
    }

    protected function needsStyleInformation(): bool
    {
        return $this->contains(fn (Column $column): bool => $column->needsStyleInformation());
    }
}
