<?php

declare(strict_types=1);

namespace Maatwebsite\Excel\Columns;

use Closure;
use Illuminate\Support\Str;
use Illuminate\Support\Traits\Macroable;
use PhpOffice\PhpSpreadsheet\Cell\Cell;
use PhpOffice\PhpSpreadsheet\Cell\Coordinate;
use PhpOffice\PhpSpreadsheet\Exception;
use PhpOffice\PhpSpreadsheet\Worksheet\Worksheet;

class Column
{
    use Commentable;
    use Filterable;
    use Macroable;
    use Readable;
    use Sizeable;
    use Styleable;
    use Writeable;

    protected int $index = 1;

    protected string $letter = 'A';

    protected ?string $type = null;

    protected ?string $format = null;

    /**
     * Tri-state: null means the column has no opinion, so a sheet-wide concern
     * such as WithFormatData decides.
     */
    protected ?bool $formatted = null;

    protected ?Closure $cellStyling = null;

    /**
     * @var list<Column>
     */
    protected array $columns = [];

    /**
     * Tri-state: null means WithStrictNullComparison decides.
     */
    protected ?bool $nullable = null;

    protected ?string $key = null;

    final protected function __construct(
        protected string $title,
        protected string|Closure $attribute,
    ) {
    }

    /**
     * @return static
     */
    public static function make(string $title, string|Closure|null $attribute = null): self
    {
        return tap(new static($title, $attribute ?: Str::snake($title)), function (Column $column): void {
            $column->configure();
        });
    }

    /**
     * @return static
     */
    public static function multiple(Column ...$columns): self
    {
        return tap(new static('', ''), function (Column $column) use ($columns): void {
            $column->columns = array_values($columns);
            $column->configure();
        });
    }

    public function type(string $type): static
    {
        $this->type = $type;

        return $this;
    }

    public function format(string $format): static
    {
        $this->format = $format;

        return $this;
    }

    public function title(): string
    {
        return $this->title;
    }

    /**
     * Override the key this column is read back under, and the heading it is
     * matched against on a WithHeadingRow import.
     */
    public function key(string $key): static
    {
        $this->key = $key;

        return $this;
    }

    /**
     * The key this column is read back under.
     */
    public function getKey(): string
    {
        return $this->key ?? $this->defaultKey();
    }

    /**
     * The heading this column matches on a WithHeadingRow import. Deliberately not
     * getKey(): renaming the output key shouldn't change which heading is matched.
     * Use an explicit array key on columns() to match a different heading.
     */
    public function headingKey(): string
    {
        return $this->defaultKey();
    }

    /**
     * Whether this column produces a value when reading. Columns without a title
     * and without an explicit key are placeholders, not data.
     */
    public function isReadable(): bool
    {
        return $this->getKey() !== '';
    }

    public function formatted(): static
    {
        $this->formatted = true;

        return $this;
    }

    public function nullable(): static
    {
        $this->nullable = true;

        return $this;
    }

    /**
     * Fill in whatever this column left undecided from the sheet-wide concerns.
     */
    public function applyDefaults(bool $formatData = false, bool $nullable = false): static
    {
        $this->formatted ??= $formatData;
        $this->nullable ??= $nullable;

        foreach ($this->columns as $column) {
            $column->applyDefaults($formatData, $nullable);
        }

        return $this;
    }

    /**
     * @throws Exception
     */
    public function index(int $index): static
    {
        $this->index  = $index;
        $this->letter = Coordinate::stringFromColumnIndex($index);

        return $this;
    }

    /**
     * @throws Exception
     */
    public function column(string $column): static
    {
        $this->index  = Coordinate::columnIndexFromString($column);
        $this->letter = $column;

        return $this;
    }

    public function letter(): string
    {
        return $this->letter;
    }

    /**
     * Whether reading this column requires the sheet to be loaded with style
     * information, which forces the reader out of read-only mode.
     */
    public function needsStyleInformation(): bool
    {
        foreach ($this->columns as $column) {
            if ($column->needsStyleInformation()) {
                return true;
            }
        }

        return false;
    }

    public function hasMultiple(): bool
    {
        return count($this->columns) > 0;
    }

    /**
     * @return list<Column>
     */
    public function columns(): array
    {
        if (!$this->hasMultiple()) {
            return [$this];
        }

        return $this->columns;
    }

    public function getIndex(): int
    {
        return $this->index;
    }

    public function withCellStyling(Closure $cellStyling): static
    {
        $this->cellStyling = $cellStyling;

        return $this;
    }

    public function writeStyles(Worksheet $worksheet, int $firstDataRow = 1): void
    {
        $style = $this->getStyle();

        if ($style === null) {
            return;
        }

        $range = $this->dataRange($worksheet, $firstDataRow);

        if ($range === null) {
            return;
        }

        $worksheet->getStyle($range)->applyFromArray($style);
    }

    /**
     * Hook for subclasses that need to set themselves up after construction.
     */
    protected function configure(): void
    {
    }

    /**
     * The attribute the column reads from, falling back to a slug of the title
     * when the attribute is a callback.
     */
    protected function defaultKey(): string
    {
        if (is_string($this->attribute) && $this->attribute !== '') {
            return $this->attribute;
        }

        return Str::snake($this->title);
    }

    protected function writeCellStyle(Cell $cell, mixed $data): void
    {
        (new CellStyle)->apply($cell, $data, $this->cellStyling);
    }

    protected function formatColumn(Worksheet $worksheet, int $firstDataRow = 1): void
    {
        if ($this->format === null) {
            return;
        }

        $range = $this->dataRange($worksheet, $firstDataRow);

        if ($range === null) {
            return;
        }

        $worksheet->getStyle($range)->getNumberFormat()->setFormatCode($this->format);
    }

    /**
     * The range this column's data occupies, or null when it holds no data.
     */
    protected function dataRange(Worksheet $worksheet, int $firstDataRow): ?string
    {
        $highestRow = $worksheet->getHighestRow();

        if ($highestRow < $firstDataRow) {
            return null;
        }

        return $this->letter . $firstDataRow . ':' . $this->letter . $highestRow;
    }
}
