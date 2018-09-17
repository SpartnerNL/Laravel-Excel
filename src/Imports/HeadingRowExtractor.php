<?php

namespace Maatwebsite\Excel\Imports;

use Illuminate\Support\Collection;
use Maatwebsite\Excel\Concerns\WithHeadingRow;
use Maatwebsite\Excel\Concerns\WithStartRow;
use Maatwebsite\Excel\Row;
use PhpOffice\PhpSpreadsheet\Worksheet\Worksheet;

class HeadingRowExtractor
{
    public const DEFAULT_HEADING_ROW = 1;

    /**
     * @param WithHeadingRow|mixed $importable
     *
     * @return int
     */
    public static function headingRow($importable): int
    {
        return method_exists($importable, 'headingRow')
            ? $importable->headingRow()
            : self::DEFAULT_HEADING_ROW;
    }

    /**
     * @param WithHeadingRow|mixed $importable
     *
     * @return int
     */
    public static function determineStartRow($importable): int
    {
        if ($importable instanceof WithStartRow) {
            return $importable->startRow();
        }

        // The start row is the row after the heading row if we have one!
        return $importable instanceof WithHeadingRow
            ? self::headingRow($importable) + 1
            : 1;
    }

    /**
     * @param Worksheet            $worksheet
     * @param WithHeadingRow|mixed $importable
     *
     * @return array
     */
    public static function extract(Worksheet $worksheet, $importable): array
    {
        if (!$importable instanceof WithHeadingRow) {
            return [];
        }

        $headingRowNumber = self::headingRow($importable);

        foreach ($worksheet->getRowIterator($headingRowNumber, $headingRowNumber + 1) as $row) {
             return (new Row($row))
                ->toCollection(null, false, false)
                ->map(function($value) {
                    return str_slug($value);
                })
                ->toArray();
        }

        return [];
    }
}