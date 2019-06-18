<?php

namespace Seoperin\LaravelExcel\Imports;

use Seoperin\LaravelExcel\Row;
use Seoperin\LaravelExcel\Concerns\ToModel;
use Seoperin\LaravelExcel\Concerns\WithMapping;
use Seoperin\LaravelExcel\Concerns\WithProgressBar;
use Seoperin\LaravelExcel\Concerns\WithBatchInserts;
use PhpOffice\PhpSpreadsheet\Worksheet\Worksheet;
use Seoperin\LaravelExcel\Concerns\WithCalculatedFormulas;

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
     */
    public function import(Worksheet $worksheet, ToModel $import, int $startRow = 1)
    {
        $headingRow = HeadingRowExtractor::extract($worksheet, $import);
        $batchSize  = $import instanceof WithBatchInserts ? $import->batchSize() : 1;
        $endRow     = EndRowFinder::find($import, $startRow);

        $i = 0;
        foreach ($worksheet->getRowIterator($startRow, $endRow) as $spreadSheetRow) {
            $i++;

            $row      = new Row($spreadSheetRow, $headingRow);
            $rowArray = $row->toArray(null, $import instanceof WithCalculatedFormulas);

            if ($import instanceof WithMapping) {
                $rowArray = $import->map($rowArray);
            }

            $this->manager->add(
                $row->getIndex(),
                $rowArray
            );

            // Flush each batch.
            if (($i % $batchSize) === 0) {
                $this->manager->flush($import, $batchSize > 1);
                $i = 0;
            }

            if ($import instanceof WithProgressBar) {
                $import->getConsoleOutput()->progressAdvance();
            }
        }

        // Flush left-overs.
        $this->manager->flush($import, $batchSize > 1);
    }
}
