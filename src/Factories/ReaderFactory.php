<?php

namespace Maatwebsite\Excel\Factories;

use Maatwebsite\Excel\Concerns\MapsCsvSettings;
use Maatwebsite\Excel\Concerns\WithCustomCsvSettings;
use Maatwebsite\Excel\Concerns\WithLimit;
use Maatwebsite\Excel\Concerns\WithReadFilter;
use Maatwebsite\Excel\Concerns\WithStartRow;
use Maatwebsite\Excel\Exceptions\NoTypeDetectedException;
use Maatwebsite\Excel\Files\TemporaryFile;
use Maatwebsite\Excel\Filters\LimitFilter;
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
    public static function make(?object $import, TemporaryFile $file, ?string $readerType = null): IReader
    {
        $reader = IOFactory::createReader(
            $readerType ?: self::identify($file)
        );

        $reader->setReadDataOnly(config('excel.imports.read_only', true));
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
                $import instanceof WithStartRow ? $import->startRow() : 1,
                $import->limit()
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
