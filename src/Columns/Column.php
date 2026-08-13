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

    protected bool $formatted = false;

    protected ?Closure $cellStyling = null;

    /**
     * @var list<Column>
     */
    protected array $columns = [];

    protected bool $nullable = false;

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
     * @throws Exception
     */
    public function coordinate(string|int $index): static
    {
        if (is_int($index)) {
            return $this->index($index);
        }

        return $this->column($index);
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

    public function attribute(): string|Closure
    {
        return $this->attribute;
    }

    /**
     * Whether reading this column requires the sheet to be loaded with style
     * information, which forces the reader out of read-only mode.
     */
    public function needsStyleInformation(): bool
    {
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

    public function writeStyles(Worksheet $worksheet): void
    {
        $style = $this->getStyle();

        if ($style === null) {
            return;
        }

        $worksheet->getStyle($this->letter)->applyFromArray($style);
    }

    /**
     * Hook for subclasses that need to set themselves up after construction.
     */
    protected function configure(): void
    {
    }

    protected function writeCellStyle(Cell $cell, mixed $data): void
    {
        (new CellStyle)->apply($cell, $data, $this->cellStyling);
    }

    protected function formatColumn(Worksheet $worksheet): void
    {
        if ($this->format === null) {
            return;
        }

        $worksheet->getStyle($this->letter)->getNumberFormat()->setFormatCode($this->format);
    }
}
