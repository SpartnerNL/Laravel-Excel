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
     *
     * @throws \Throwable
     */
    public function import(Worksheet $worksheet, ToModel $import)
    {
        $batchSize = $import instanceof WithBatchInserts ? $import->batchSize() : 1;

        $i = 0;
        foreach ($worksheet->getRowIterator() as $row) {
            $i++;

            $this->manager->add(
                $import->model((new Row($row))->toArray())
            );

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
