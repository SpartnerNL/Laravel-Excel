<?php

declare(strict_types=1);

namespace Maatwebsite\Excel\Columns;

use Illuminate\Support\Arr;
use Illuminate\Support\Collection;
use Maatwebsite\Excel\Concerns\WithColumns;
use Maatwebsite\Excel\Concerns\WithFormatData;
use Maatwebsite\Excel\Concerns\WithHeadingRow;
use Maatwebsite\Excel\Concerns\WithStrictNullComparison;
use Maatwebsite\Excel\Exceptions\ColumnCollisionException;
use Maatwebsite\Excel\Helpers\ConcernTree;
use PhpOffice\PhpSpreadsheet\Cell\Coordinate;
use PhpOffice\PhpSpreadsheet\Exception;
use PhpOffice\PhpSpreadsheet\Worksheet\Worksheet;

/**
 * @extends Collection<string, Column>
 */
class ColumnCollection extends Collection
{
    /**
     * Columns that were declared but have no place on the sheet, because no
     * heading matched them. They are read back as null, in declaration order.
     *
     * @var array<string, Column>
     */
    protected array $unmatched = [];

    /**
     * @param  list<string>|null  $headingRow
     *
     * @throws Exception
     * @throws ColumnCollisionException
     */
    public static function makeFrom(object $concernable, ?array $headingRow = null, int $columnOffset = 0): self
    {
        if (!$concernable instanceof WithColumns) {
            return new self([]);
        }

        $headingMap = static::headingMap($headingRow);

        // Sheet-wide concerns act as the default for every column that didn't
        // decide for itself.
        $formatData = $concernable instanceof WithFormatData;
        $nullable   = $concernable instanceof WithStrictNullComparison;

        $index     = 0;
        $columns   = [];
        $unmatched = [];

        foreach ($concernable->columns() as $key => $column) {
            if (is_array($column)) {
                $column = Column::multiple(...$column);
            }

            $column = $column->applyDefaults($formatData, $nullable);

            $index++;

            if ($concernable instanceof WithHeadingRow) {
                $heading = is_int($key) ? $column->headingKey() : $key;

                if (!isset($headingMap[$heading])) {
                    $unmatched[$column->getKey()] = $column;

                    continue;
                }

                $column = $column->column($headingMap[$heading]);
            } elseif (is_int($key)) {
                // Positional columns follow the start cell; explicit letters are absolute.
                $column = $column->index($index + $columnOffset);
            } else {
                $column = $column->column($key);
            }

            if (isset($columns[$column->letter()])) {
                throw ColumnCollisionException::atLetter($column->letter());
            }

            $columns[$column->letter()] = $column;
        }

        $collection            = new self($columns);
        $collection->unmatched = $unmatched;

        return $collection;
    }

    /**
     * Whether any column of this concernable requires the sheet to be loaded with
     * style information.
     *
     * Deliberately inspects the raw column definitions rather than a built
     * collection: the heading row isn't known before the file is read, so
     * makeFrom() cannot place the columns yet.
     */
    public static function requiresStyleInformation(?object $concernable): bool
    {
        foreach (ConcernTree::flatten($concernable) as $node) {
            if (!$node instanceof WithColumns) {
                continue;
            }

            foreach ($node->columns() as $column) {
                foreach (Arr::wrap($column) as $definition) {
                    if ($definition->needsStyleInformation()) {
                        return true;
                    }
                }
            }
        }

        return false;
    }

    /**
     * @throws Exception
     */
    public function writeHeadings(Worksheet $worksheet, int $row): void
    {
        $this->sortByColumn()->each(function (Column $column) use ($worksheet, $row): void {
            $column->writeHeading($worksheet, $row);
        });
    }

    /**
     * @throws Exception
     */
    public function afterWriting(Worksheet $worksheet, int $headingRow = 1): void
    {
        $firstDataRow = $headingRow + 1;

        $this->sortByColumn()->filter(fn (Column $column): bool => $column->hasAutoFilter())->pipe(function (self $columns) use ($worksheet, $headingRow): void {
            if ($columns->isEmpty()) {
                return;
            }

            $start = $columns->first();
            $end   = $columns->last();

            $worksheet->setAutoFilter(
                $start->letter() . $headingRow . ':' . $end->letter() . $worksheet->getHighestRow()
            );
        });

        $this->each(function (Column $column) use ($worksheet, $firstDataRow): void {
            $column->afterWriting($worksheet, $firstDataRow);
        });
    }

    /**
     * Headings in declaration order, keyed by column letter.
     *
     * @return array<string, string>
     */
    public function headings(): array
    {
        $headings = [];

        foreach ($this->sortByColumn() as $letter => $column) {
            $headings[$letter] = $column->title();
        }

        return $headings;
    }

    /**
     * Keys of the columns that have no place on the sheet.
     *
     * @return list<string>
     */
    public function unmatchedKeys(): array
    {
        return array_keys($this->unmatched);
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

    /**
     * @param  list<string>|null  $headingRow
     * @return array<string, string>
     */
    protected static function headingMap(?array $headingRow): array
    {
        $map = [];

        foreach ($headingRow ?? [] as $index => $heading) {
            // First heading wins, so a duplicate never steals the column.
            $map[$heading] ??= Coordinate::stringFromColumnIndex($index + 1);
        }

        return $map;
    }
}
