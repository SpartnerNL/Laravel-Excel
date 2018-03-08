<?php

namespace Maatwebsite\Excel\Fakes;

use Maatwebsite\Excel\Exporter;

class ExcelFake implements Exporter
{
    /**
     * {@inheritdoc}
     */
    public function download($export, string $fileName, string $writerType = null)
    {
        //
    }

    /**
     * {@inheritdoc}
     */
    public function store($export, string $filePath, string $disk = null, string $writerType = null)
    {
        //
    }

    /**
     * {@inheritdoc}
     */
    public function queue($export, string $filePath, string $disk = null, string $writerType = null)
    {
        //
    }
}
