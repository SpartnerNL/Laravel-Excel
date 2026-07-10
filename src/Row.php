<?php

namespace Maatwebsite\Excel;

use ArrayAccess;
use Closure;
use Illuminate\Support\Collection;
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

    /**
     * @param  list<string>  $headingRow
     * @param  array<int, bool>  $headerIsGrouped
     */
    public function __construct(
        protected SpreadsheetRow $row,
        protected array $headingRow = [],
        protected array $headerIsGrouped = [],
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
     */
    public function toArray(mixed $nullValue = null, bool $calculateFormulas = false, bool $formatData = true, ?string $endColumn = null): array
    {
        if (is_array($this->rowCache) && ($this->rowCacheFormatData === $formatData) && ($this->rowCacheEndColumn === $endColumn)) {
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

        if ($this->preparationCallback instanceof Closure) {
            $cells = ($this->preparationCallback)($cells, $this->row->getRowIndex());
        }

        $this->rowCache           = $cells;
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
}
