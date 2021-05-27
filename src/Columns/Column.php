<?php

namespace Maatwebsite\Excel\Columns;

use Illuminate\Support\Str;
use Illuminate\Support\Traits\Macroable;
use PhpOffice\PhpSpreadsheet\Cell\Coordinate;
use PhpOffice\PhpSpreadsheet\Worksheet\Worksheet;

class Column
{
    use Sizeable;
    use Styleable;
    use Macroable;
    use Readable;
    use Writeable;
    use Filterable;

    /**
     * @var int
     */
    protected $index;

    /**
     * @var string
     */
    protected $letter;

    /**
     * @var string
     */
    protected $title;

    /**
     * @var string|callable
     */
    protected $attribute;

    /**
     * @var string
     */
    protected $type;

    /**
     * @var string|null
     */
    protected $format;

    /**
     * @var bool
     */
    protected $formatted = false;

    /**
     * @var callable|null
     */
    protected $cellStyling;

    /**
     * @var Column[]
     */
    protected $columns = [];

    /**
     * @param string          $title
     * @param string|callable $attribute
     */
    protected function __construct(string $title, $attribute)
    {
        $this->title     = $title;
        $this->attribute = $attribute;
    }

    /**
     * @param string                    $title
     * @param string|callable|EmptyCell $attribute
     *
     * @return static
     */
    public static function make(string $title, $attribute = null)
    {
        return new static($title, $attribute ?: Str::snake($title));
    }

    /**
     * @return static
     */
    public static function multiple(Column ...$columns)
    {
        return tap(new static('', ''), function (Column $column) use ($columns) {
            $column->columns = $columns;
        });
    }

    /**
     * @param string $type
     *
     * @return $this
     */
    public function type(string $type)
    {
        $this->type = $type;

        return $this;
    }

    /**
     * @param string $format
     *
     * @return $this
     */
    public function format(string $format)
    {
        $this->format = $format;

        return $this;
    }

    public function title(): string
    {
        return $this->title;
    }

    /**
     * @return $this
     */
    public function formatted()
    {
        $this->formatted = true;

        return $this;
    }

    /**
     * @param string|int $index
     *
     * @return $this
     */
    public function coordinate($index)
    {
        if (is_numeric($index)) {
            return $this->index($index);
        }

        return $this->column($index);
    }

    /**
     * @return $this
     */
    public function index(int $index)
    {
        $this->index  = $index;
        $this->letter = Coordinate::stringFromColumnIndex($index);

        return $this;
    }

    /**
     * @return $this
     */
    public function column(string $column)
    {
        $this->index  = Coordinate::columnIndexFromString($column);
        $this->letter = $column;

        return $this;
    }

    public function letter(): string
    {
        return $this->letter;
    }

    public function attribute(): string
    {
        return $this->attribute;
    }

    public function needsStyleInformation(): bool
    {
        return false;
    }

    public function hasMultiple(): bool
    {
        return count($this->columns) > 0;
    }

    /**
     * @return Column[]
     */
    public function columns(): array
    {
        if (!$this->hasMultiple()) {
            return [$this];
        }

        return $this->columns;
    }

    public function getIndex(): ?int
    {
        return $this->index;
    }

    protected function formatColumn(Worksheet $worksheet)
    {
        if (null === $this->format) {
            return;
        }

        $worksheet->getStyle($this->letter)->getNumberFormat()->setFormatCode($this->format);
    }
}
