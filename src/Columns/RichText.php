<?php

namespace Maatwebsite\Excel\Columns;

use Maatwebsite\Excel\Helpers\RichTextReader;
use PhpOffice\PhpSpreadsheet\Cell\Cell;
use PhpOffice\PhpSpreadsheet\Cell\DataType;
use PhpOffice\PhpSpreadsheet\Helper\Html;

class RichText extends Column
{
    protected $type = DataType::TYPE_INLINE;

    protected function toExcelValue($value)
    {
        return (new Html())->toRichTextObject($value);
    }

    public function read(Cell $cell)
    {
        return RichTextReader::toHtml($cell);
    }
}
