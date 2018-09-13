<?php

namespace Maatwebsite\Excel\Imports;

use Maatwebsite\Excel\Concerns\WithBatchInserts;
use Maatwebsite\Excel\Row;
use Maatwebsite\Excel\Concerns\ToModel;
use PhpOffice\PhpSpreadsheet\Worksheet\Worksheet;

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
     * @param int|null  $endRow
     *
     * @throws \PhpOffice\PhpSpreadsheet\Exception
     * @throws \Throwable
     */
    public function import(Worksheet $worksheet, ToModel $import, int $startRow = 1)
    {
        $batchSize = $import instanceof WithBatchInserts ? $import->batchSize() : 1;

        $i = 0;
        foreach ($worksheet->getRowIterator()->resetStart($startRow ?? 1) as $spreadSheetRow) {
            $i++;

            $row = new Row($spreadSheetRow);
            $this->manager->add($import->model($row->toArray()));

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
