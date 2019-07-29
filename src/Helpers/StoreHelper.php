<?php

namespace Maatwebsite\Excel\Helpers;

use Maatwebsite\Excel\Writer;
use Maatwebsite\Excel\Files\Disk;

class StoreHelper
{
    /**
     * @param object        $export
     * @param Writer        $writer
     * @param Disk          $disk
     * @param string        $filePath
     * @param string|null   $writerType
     *
     * @throws \Maatwebsite\Excel\Exceptions\NoTypeDetectedException
     * @throws \PhpOffice\PhpSpreadsheet\Exception
     * @return bool
     */
    public static function store($export, Writer $writer, Disk $disk, string $filePath, string $writerType = null)
    {
        $writerType = FileTypeDetector::detectStrict($filePath, $writerType);

        $temporaryFile = $writer->export($export, $writerType);

        $exported = $disk->copy($temporaryFile, $filePath);
        $temporaryFile->delete();

        return $exported;
    }
}
