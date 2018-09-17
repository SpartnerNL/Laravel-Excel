<?php

namespace Maatwebsite\Excel\Imports;

use Maatwebsite\Excel\Row;
use Maatwebsite\Excel\Concerns\ToModel;
use Maatwebsite\Excel\Concerns\WithBatchInserts;
use PhpOffice\PhpSpreadsheet\Worksheet\Worksheet;
use Maatwebsite\Excel\Concerns\WithCalculatedFormulas;

class ModelImporter
{
    /**
     * @var ModelManager
     */
    private $manager;

    /**
     * @param ModelManager $manager
     */
    public function __construct(ModelManager $manager)
    {
        $this->manager = $manager;
    }

    /**
     * @param Worksheet $worksheet
     * @param ToModel   $import
     * @param int|null  $startRow
     *
     * @throws \PhpOffice\PhpSpreadsheet\Exception
     * @throws \Throwable
     */
    public function import(Worksheet $worksheet, ToModel $import, int $startRow = 1)
    {
        $headingRow = HeadingRowExtractor::extract($worksheet, $import);
        $batchSize  = $import instanceof WithBatchInserts ? $import->batchSize() : 1;

        $i = 0;
        foreach ($worksheet->getRowIterator()->resetStart($startRow) as $spreadSheetRow) {
            $i++;

            $row   = new Row($spreadSheetRow, $headingRow);
            $model = $import->model($row->toArray(null, $import instanceof WithCalculatedFormulas));

            // Skip rows that the user explicitly returned null for
            if (null !== $model) {
                $this->manager->add($model);
            }

            // Flush each batch.
            if (($i % $batchSize) === 0) {
                $this->manager->flush($batchSize > 1);
                $i = 0;
            }
        }

        // Flush left-overs.
        $this->manager->flush();
    }
}
