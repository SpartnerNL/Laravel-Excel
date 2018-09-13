<?php

namespace Maatwebsite\Excel;

use Maatwebsite\Excel\Concerns\WithChunkReading;
use Maatwebsite\Excel\Concerns\WithMultipleSheets;
use Maatwebsite\Excel\Filters\ChunkReadFilter;
use PhpOffice\PhpSpreadsheet\Reader\IReader;

class ChunkReader
{

    /**
     * @param WithChunkReading $import
     * @param IReader          $reader
     * @param string           $file
     */
    public function read(WithChunkReading $import, IReader $reader, string $file)
    {
        $worksheets = $this->getWorksheets($import, $reader, $file);

        foreach ($worksheets as $name => $sheetImport) {
            $totalRows = $this->getTotalRows($reader, $file, $name);

            for ($startRow = 1; $startRow <= $totalRows; $startRow += $import->chunkSize()) {
                $reader->setReadDataOnly(true);
                $reader->setReadEmptyCells(false);
                $reader->setReadFilter(new ChunkReadFilter($startRow, $import->chunkSize(), $name));

                $spreadsheet = $reader->load($file);

                $sheet = new Sheet($spreadsheet->getSheetByName($name));
                $sheet->import($sheetImport, $startRow, $startRow + $import->chunkSize());
                $sheet->disconnect();
            }
        }
    }

    /**
     * @param WithChunkReading $import
     * @param IReader          $reader
     * @param string           $file
     *
     * @return array
     */
    private function getWorksheets(WithChunkReading $import, IReader $reader, string $file): array
    {
        if (!method_exists($reader, 'listWorksheetNames')) {
            return ['' => $import];
        }

        $worksheets     = [];
        $worksheetNames = $reader->listWorksheetNames($file);
        if ($import instanceof WithMultipleSheets) {
            $sheetImports = $import->sheets();

            // Load specific sheets.
            if (method_exists($reader, 'setLoadSheetsOnly')) {
                $reader->setLoadSheetsOnly(array_keys($sheetImports));
            }

            foreach ($sheetImports as $index => $sheetImport) {
                if (is_numeric($index)) {
                    $index = $worksheetNames[$index] ?? $index;
                }

                // Specify with worksheet name should have which import.
                $worksheets[$index] = $sheetImport;
            }
        } else {
            // Each worksheet the same import class.
            foreach ($worksheetNames as $name) {
                $worksheets[$name] = $import;
            }
        }

        return $worksheets;
    }

    /**
     * @param IReader $reader
     * @param string  $file
     * @param string  $name
     *
     * @return int|null
     */
    private function getTotalRows(IReader $reader, string $file, string $name)
    {
        $info = $reader->listWorksheetInfo($file);

        foreach ($info as $sheet) {
            if ($sheet['worksheetName'] ?? '' === $name) {
                return $sheet['totalRows'] ?? null;
            }
        }

        return null;
    }
}