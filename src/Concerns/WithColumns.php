<?php

declare(strict_types=1);

namespace Maatwebsite\Excel\Concerns;

use Maatwebsite\Excel\Columns\Column;

/**
 * Describe the sheet as a list of typed columns instead of raw arrays. Each column
 * knows its own data type, number format, styling and casting, so it applies on the
 * way out and on the way back in.
 *
 * Array keys are optional. When given, they are either a column letter (`'B'`) or,
 * in combination with WithHeadingRow, the heading to match:
 *
 *     public function columns(): array
 *     {
 *         return [
 *             Number::make('ID', 'id'),
 *             Text::make('Full Name', 'name'),
 *             'D' => Date::make('Signed Up', 'created_at'),
 *         ];
 *     }
 *
 * A column's title is the heading that is written; its attribute is where the value
 * comes from and, unless overridden with `key()`, the key it is read back under.
 *
 * On export, `WithCustomStartCell` shifts positionally declared columns; columns
 * given an explicit letter stay where they are put. Column-level formats and widths
 * are applied before `WithColumnFormatting`, `WithColumnWidths` and `WithStyles`,
 * so those concerns still win where they overlap. `WithStyles` is the way to style
 * the heading row.
 *
 * Cannot be combined with `WithHeadings` or `WithMapping` (export), or with
 * `WithMappedCells`, `WithColumnLimit` or `WithGroupedHeadingRow` (import) — the
 * columns already describe what those concerns describe.
 */
interface WithColumns
{
    /**
     * @return array<array-key, Column|list<Column>>
     */
    public function columns(): array;
}
