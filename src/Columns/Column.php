<?php

namespace Maatwebsite\Excel\Columns;

use Illuminate\Support\Str;
use Illuminate\Support\Traits\Macroable;
use PhpOffice\PhpSpreadsheet\Cell\Cell;
use PhpOffice\PhpSpreadsheet\Cell\Coordinate;
use PhpOffice\PhpSpreadsheet\Worksheet\Worksheet;

class Column
{
    use Sizeable;
    use Styleable;
    use Macroable;
    use Writeable;
    use Filterable;
    use Commentable;

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

    /**
     * @param Cell $cell
     *
     * @return mixed
     */
    public function read(Cell $cell)
    {
        if ($this->formatted) {
            $cell->getStyle()->getNumberFormat()->setFormatCode($this->format);

            $value = $cell->getFormattedValue();
        } else {
            $value = $cell->getCalculatedValue();
        }

        $value = $this->cast($value);

        if (is_callable($this->attribute)) {
            return ($this->attribute)($value);
        }

        return $value;
    }

    public function title(): string
    {
        return $this->title;
    }

    public function shouldAutoSize(): bool
    {
        return $this->autoSize;
    }

    /**
     * @param callable $cellStyle
     *
     * @return $this
     */
    public function withCellStyling(callable $cellStyle)
    {
        $this->cellStyling = $cellStyle;

        return $this;
    }

    /**
     * @return $this
     */
    public function formatted()
    {
        $this->formatted = true;

        return $this;
    }

    public function index(int $index)
    {
        $this->index  = $index;
        $this->letter = Coordinate::stringFromColumnIndex($index);

        return $this;
    }

    /**
     * @param mixed $data
     *
     * @return mixed
     */
    protected function resolveValue($data)
    {
        if (is_callable($this->attribute)) {
            return ($this->attribute)($data);
        }

        return $this->toExcelValue(
            $data[$this->attribute] ?? null
        );
    }

    protected function formatColumn(Worksheet $worksheet)
    {
        if (null === $this->format) {
            return;
        }

        $worksheet->getStyle($this->letter)->getNumberFormat()->setFormatCode($this->format);
    }

    protected function writeCellStyle(Cell $cell, $data)
    {
        (new CellStyle())->apply($cell, $data, $this->cellStyling);
    }

    /**
     * @param mixed $value
     *
     * @return mixed
     */
    protected function cast($value)
    {
        return $value;
    }

    /**
     * @param mixed $value
     *
     * @return mixed
     */
    protected function toExcelValue($value)
    {
        return $value;
    }
}
