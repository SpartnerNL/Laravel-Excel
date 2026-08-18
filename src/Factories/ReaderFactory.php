<?php

namespace Maatwebsite\Excel\Factories;

use Maatwebsite\Excel\Columns\ColumnCollection;
use Maatwebsite\Excel\Concerns\Import;
use Maatwebsite\Excel\Concerns\MapsCsvSettings;
use Maatwebsite\Excel\Concerns\WithCustomCsvSettings;
use Maatwebsite\Excel\Concerns\WithHeadingRow;
use Maatwebsite\Excel\Concerns\WithLimit;
use Maatwebsite\Excel\Concerns\WithReadFilter;
use Maatwebsite\Excel\Exceptions\NoTypeDetectedException;
use Maatwebsite\Excel\Files\TemporaryFile;
use Maatwebsite\Excel\Filters\LimitFilter;
use Maatwebsite\Excel\Imports\HeadingRowExtractor;
use PhpOffice\PhpSpreadsheet\IOFactory;
use PhpOffice\PhpSpreadsheet\Reader\Csv;
use PhpOffice\PhpSpreadsheet\Reader\Exception;
use PhpOffice\PhpSpreadsheet\Reader\IReader;

class ReaderFactory
{
    use MapsCsvSettings;

    /**
     * @throws Exception
     * @throws NoTypeDetectedException
     */
    public static function make(?Import $import, TemporaryFile $file, ?string $readerType = null): IReader
    {
        $reader = IOFactory::createReader(
            $readerType ?: self::identify($file)
        );

        // Columns such as RichText, Image and Hyperlink read information that
        // PhpSpreadsheet only loads outside of read-only mode, so an import that
        // declares one opts itself out of the global setting.
        $reader->setReadDataOnly(
            config('excel.imports.read_only', true)
            && !ColumnCollection::requiresStyleInformation($import)
        );

        $reader->setReadEmptyCells(!config('excel.imports.ignore_empty', false));

        if ($reader instanceof Csv) {
            static::applyCsvSettings(config('excel.imports.csv', []));

            if ($import instanceof WithCustomCsvSettings) {
                static::applyCsvSettings($import->getCsvSettings());
            }

            $reader->setDelimiter(static::$delimiter);
            $reader->setEnclosure(static::$enclosure);
            $reader->setEscapeCharacter(static::$escapeCharacter);
            $reader->setContiguous(static::$contiguous);
            $reader->setInputEncoding(static::$inputEncoding);
            $reader->setTestAutoDetect(static::$testAutoDetect);
        }

        if ($import instanceof WithReadFilter) {
            $reader->setReadFilter($import->readFilter());
        } elseif ($import instanceof WithLimit) {
            $reader->setReadFilter(new LimitFilter(
                HeadingRowExtractor::determineStartRow($import),
                $import->limit(),
                $import instanceof WithHeadingRow ? HeadingRowExtractor::headingRow($import) : null
            ));
        }

        return $reader;
    }

    /**
     * @throws NoTypeDetectedException
     */
    private static function identify(TemporaryFile $temporaryFile): string
    {
        try {
            /** @throws Exception */
            return IOFactory::identify($temporaryFile->getLocalPath());
        } catch (Exception $e) {
            throw new NoTypeDetectedException('', 0, $e);
        }
    }
}
