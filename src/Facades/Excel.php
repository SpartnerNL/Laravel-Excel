<?php

namespace Maatwebsite\Excel\Facades;

use Illuminate\Support\Facades\Facade;
use Symfony\Component\HttpFoundation\BinaryFileResponse;

/**
 * @method static BinaryFileResponse download(object $export, string $fileName, string $writerType = null)
 * @method static bool store(object $export, string $filePath, string $disk = null, string $writerType = null)
 * @method static bool queue(object $export, string $filePath, string $disk = null, string $writerType = null)
 */
class Excel extends Facade
{
    /**
     * Get the registered name of the component.
     *
     * @return string
     */
    protected static function getFacadeAccessor()
    {
        return 'excel';
    }
}
