<?php

namespace Maatwebsite\Excel;

use Illuminate\Support\Collection;
use Maatwebsite\Excel\Concerns\ToArray;
use Maatwebsite\Excel\Concerns\ToCollection;
use Maatwebsite\Excel\Concerns\ToModel;
use Maatwebsite\Excel\Concerns\WithCalculatedFormulas;
use Maatwebsite\Excel\Concerns\WithFormatData;
use Maatwebsite\Excel\Concerns\WithMappedCells;
use PhpOffice\PhpSpreadsheet\Exception;
use PhpOffice\PhpSpreadsheet\Worksheet\Worksheet;

class MappedReader
{
    /**
     * @throws Exception
     */
    public function map(WithMappedCells $import, Worksheet $worksheet): void
    {
        $mapped = $import->mapping();
        array_walk_recursive($mapped, function (&$coordinate) use ($import, $worksheet): void {
            $cell = Cell::make($worksheet, $coordinate);

            $coordinate = $cell->getValue(
                null,
                $import instanceof WithCalculatedFormulas,
                $import instanceof WithFormatData
            );
        });

        if ($import instanceof ToModel) {
            $model = $import->model($mapped);

            if ($model) {
                $model->saveOrFail();
            }
        }

        if ($import instanceof ToCollection) {
            $import->collection(new Collection($mapped));
        }

        if ($import instanceof ToArray) {
            $import->array($mapped);
        }
    }
}
