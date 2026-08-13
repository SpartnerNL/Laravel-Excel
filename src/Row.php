<?php

namespace Maatwebsite\Excel;

use ArrayAccess;
use Closure;
use Illuminate\Support\Arr;
use Illuminate\Support\Collection;
use Maatwebsite\Excel\Columns\ColumnCollection;
use PhpOffice\PhpSpreadsheet\Calculation\Exception as CalculationException;
use PhpOffice\PhpSpreadsheet\Worksheet\Row as SpreadsheetRow;

/**
 * @implements ArrayAccess<array-key, mixed>
 *
 * @mixin SpreadsheetRow
 */
class Row implements ArrayAccess
{
    use DelegatedMacroable;

    protected ?Closure $preparationCallback = null;

    /**
     * @var array<array-key, mixed>|null
     */
    protected ?array $rowCache = null;

    protected ?bool $rowCacheFormatData = null;

    protected ?string $rowCacheEndColumn = null;

    protected mixed $rowCacheNullValue = null;

    /**
     * @param  list<string>  $headingRow
     * @param  array<int, bool>  $headerIsGrouped
     * @param  ColumnCollection|null  $columns  When given, the row is read through the
     *                                          column definitions instead of the heading row.
     */
    public function __construct(
        protected SpreadsheetRow $row,
        protected array $headingRow = [],
        protected array $headerIsGrouped = [],
        protected ?ColumnCollection $columns = null,
    ) {
    }

    public function getDelegate(): SpreadsheetRow
    {
        return $this->row;
    }

    /**
     * @return Collection<array-key, mixed>
     */
    public function toCollection(mixed $nullValue = null, bool $calculateFormulas = false, bool $formatData = true, ?string $endColumn = null): Collection
    {
        return new Collection($this->toArray($nullValue, $calculateFormulas, $formatData, $endColumn));
    }

    /**
     * @return array<array-key, mixed>
     *
     * @throws CalculationException
     */
    public function toArrayWithColumns(ColumnCollection $columns): array
    {
        // Columns without a matching heading still appear, as null, so the shape
        // of the result doesn't depend on what the file happened to contain.
        $cells = array_fill_keys($columns->unmatchedKeys(), null);

        foreach ($this->row->getCellIterator($columns->start() ?: 'A', $columns->end()) as $cell) {
            foreach (Arr::wrap($columns->get($cell->getColumn())) as $column) {
                foreach ($column->columns() as $subColumn) {
                    if ($subColumn->isReadable()) {
                        $cells[$subColumn->getKey()] = $subColumn->read($cell);
                    }
                }
            }
        }

        return $cells;
    }

    /**
     * @return array<array-key, mixed>
     */
    public function toArray(mixed $nullValue = null, bool $calculateFormulas = false, bool $formatData = true, ?string $endColumn = null): array
    {
        // Columns define their own range and read behaviour, so none of the
        // arguments apply and the result can be cached unconditionally.
        if ($this->columns instanceof ColumnCollection) {
            return $this->rowCache ??= $this->prepare(
                $this->toArrayWithColumns($this->columns)
            );
        }

        if (is_array($this->rowCache)
            && ($this->rowCacheNullValue === $nullValue)
            && ($this->rowCacheFormatData === $formatData)
            && ($this->rowCacheEndColumn === $endColumn)) {
            return $this->rowCache;
        }

        $cells = [];

        $i = 0;
        foreach ($this->row->getCellIterator('A', $endColumn) as $cell) {
            $value = (new Cell($cell))->getValue($nullValue, $calculateFormulas, $formatData);

            if (isset($this->headingRow[$i])) {
                if (!$this->headerIsGrouped[$i]) {
                    $cells[$this->headingRow[$i]] = $value;
                } else {
                    $cells[$this->headingRow[$i]][] = $value;
                }
            } else {
                $cells[] = $value;
            }

            $i++;
        }

        $cells = $this->prepare($cells);

        $this->rowCache           = $cells;
        $this->rowCacheNullValue  = $nullValue;
        $this->rowCacheFormatData = $formatData;
        $this->rowCacheEndColumn  = $endColumn;

        return $cells;
    }

    public function isEmpty(bool $calculateFormulas = false, ?string $endColumn = null): bool
    {
        return count(array_filter($this->toArray(null, $calculateFormulas, false, $endColumn))) === 0;
    }

    public function getIndex(): int
    {
        return $this->row->getRowIndex();
    }

    public function offsetExists($offset): bool
    {
        return isset($this->toArray()[$offset]);
    }

    public function offsetGet($offset): mixed
    {
        return $this->toArray()[$offset];
    }

    public function offsetSet($offset, $value): void
    {
        //
    }

    public function offsetUnset($offset): void
    {
        //
    }

    /**
     * @internal
     */
    public function setPreparationCallback(?Closure $preparationCallback = null): void
    {
        $this->preparationCallback = $preparationCallback;
    }

    /**
     * @param  array<array-key, mixed>  $cells
     * @return array<array-key, mixed>
     */
    protected function prepare(array $cells): array
    {
        if (!$this->preparationCallback instanceof Closure) {
            return $cells;
        }

        return ($this->preparationCallback)($cells, $this->row->getRowIndex());
    }
}
