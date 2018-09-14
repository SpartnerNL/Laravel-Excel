<?php

namespace Maatwebsite\Excel\Factories;

use Maatwebsite\Excel\Exceptions\UnreadableFileException;
use PhpOffice\PhpSpreadsheet\IOFactory;
use PhpOffice\PhpSpreadsheet\Reader\IReader;

class ReaderFactory
{
    /**
     * @param string      $filePath
     * @param string|null $readerType
     *
     * @return IReader
     */
    public static function make(string $filePath, string $readerType = null): IReader
    {
        $readerType = $readerType ?? IOFactory::identify($filePath);
        $reader     = IOFactory::createReader($readerType);

        if (!$reader->canRead($filePath)) {
            throw new UnreadableFileException;
        }

        return $reader;
    }
}