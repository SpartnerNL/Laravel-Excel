<?php

declare(strict_types=1);

namespace Maatwebsite\Excel\Columns;

use Closure;
use PhpOffice\PhpSpreadsheet\Cell\Cell;
use PhpOffice\PhpSpreadsheet\Cell\DataType;

class EmptyCell extends Column
{
    protected ?string $type = DataType::TYPE_NULL;

    /**
     * @return static
     */
    public static function make(?string $title = null, string|Closure|null $attribute = null): self
    {
        return parent::make($title ?: '', $attribute);
    }

    public function read(Cell $cell): mixed
    {
        return null;
    }
}
