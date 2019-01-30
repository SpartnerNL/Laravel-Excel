<?php

namespace Maatwebsite\Excel\Factories;

use PhpOffice\PhpSpreadsheet\IOFactory;
use PhpOffice\PhpSpreadsheet\Reader\IReader;

class ReaderFactory
{
    /**
     * @param string $filePath
     * @param string $readerType
     *
     * @throws \PhpOffice\PhpSpreadsheet\Reader\Exception
     * @return IReader
     */
    public static function make(string $filePath, string $readerType): IReader
    {
        return IOFactory::createReader($readerType);
    }
}
