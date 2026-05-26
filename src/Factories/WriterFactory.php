<?php

namespace Maatwebsite\Excel\Factories;

use Maatwebsite\Excel\Cache\CacheManager;
use Maatwebsite\Excel\Concerns\MapsCsvSettings;
use Maatwebsite\Excel\Concerns\WithCharts;
use Maatwebsite\Excel\Concerns\WithCustomCsvSettings;
use Maatwebsite\Excel\Concerns\WithMultipleSheets;
use Maatwebsite\Excel\Concerns\WithPreCalculateFormulas;
use PhpOffice\PhpSpreadsheet\IOFactory;
use PhpOffice\PhpSpreadsheet\Spreadsheet;
use PhpOffice\PhpSpreadsheet\Writer\Csv;
use PhpOffice\PhpSpreadsheet\Writer\Exception;
use PhpOffice\PhpSpreadsheet\Writer\Html;
use PhpOffice\PhpSpreadsheet\Writer\IWriter;

class WriterFactory
{
    use MapsCsvSettings;

    /**
     * @throws Exception
     */
    public static function make(string $writerType, Spreadsheet $spreadsheet, object $export, ?string $filePath = null): IWriter
    {
        $writer = IOFactory::createWriter($spreadsheet, $writerType);

        $writer->setUseDiskCaching(
            config('excel.cache.driver', CacheManager::DRIVER_MEMORY) !== CacheManager::DRIVER_MEMORY
        );

        if (self::includesCharts($export)) {
            $writer->setIncludeCharts(true);
        }

        if ($writer instanceof Html && $export instanceof WithMultipleSheets) {
            $writer->writeAllSheets();
        }

        if ($writer instanceof Csv) {
            static::applyCsvSettings(config('excel.exports.csv', []));

            // Auto-detect TSV files and apply tab delimiter
            if ($filePath && self::isTsvFile($filePath) && !($export instanceof WithCustomCsvSettings)) {
                static::applyCsvSettings(['delimiter' => "\t"]);
            }

            if ($export instanceof WithCustomCsvSettings) {
                static::applyCsvSettings($export->getCsvSettings());
            }

            $writer->setDelimiter(static::$delimiter);
            $writer->setEnclosure(static::$enclosure);
            $writer->setEnclosureRequired((bool) static::$enclosure);
            $writer->setLineEnding(static::$lineEnding);
            $writer->setUseBOM(static::$useBom);
            $writer->setIncludeSeparatorLine(static::$includeSeparatorLine);
            $writer->setExcelCompatibility(static::$excelCompatibility);
            $writer->setOutputEncoding(static::$outputEncoding);
        }

        // Calculation settings
        $writer->setPreCalculateFormulas(
            $export instanceof WithPreCalculateFormulas
                ? true
                : config('excel.exports.pre_calculate_formulas', false)
        );

        return $writer;
    }

    private static function includesCharts(object $export): bool
    {
        if ($export instanceof WithCharts) {
            return true;
        }

        if ($export instanceof WithMultipleSheets) {
            foreach ($export->sheets() as $sheet) {
                if ($sheet instanceof WithCharts) {
                    return true;
                }
            }
        }

        return false;
    }

    private static function isTsvFile(string $filePath): bool
    {
        $pathInfo  = pathinfo($filePath);
        $extension = strtolower($pathInfo['extension'] ?? '');

        return $extension === 'tsv';
    }
}
