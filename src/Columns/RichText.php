<?php

declare(strict_types=1);

namespace Maatwebsite\Excel\Columns;

use Maatwebsite\Excel\Helpers\RichTextReader;
use PhpOffice\PhpSpreadsheet\Cell\Cell;
use PhpOffice\PhpSpreadsheet\Cell\DataType;
use PhpOffice\PhpSpreadsheet\Helper\Html;
use PhpOffice\PhpSpreadsheet\RichText\RichText as RichTextObject;

class RichText extends Column
{
    protected ?string $type = DataType::TYPE_INLINE;

    public function read(Cell $cell): mixed
    {
        return RichTextReader::toHtml($cell);
    }

    public function needsStyleInformation(): bool
    {
        return true;
    }

    protected function toExcelValue(mixed $value): RichTextObject
    {
        // PhpSpreadsheet's html parser only descends into block level elements,
        // so bare text nodes are dropped unless we wrap them ourselves.
        return (new Html)->toRichTextObject('<div>' . $value . '</div>');
    }
}
