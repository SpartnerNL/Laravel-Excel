<?php

namespace Maatwebsite\Excel\Facades;

use Illuminate\Foundation\Bus\PendingDispatch;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Facade;
use Maatwebsite\Excel\Excel as BaseExcel;
use Maatwebsite\Excel\Fakes\ExcelFake;
use Symfony\Component\HttpFoundation\BinaryFileResponse;

/**
 * @method static BinaryFileResponse download(object $export, string $fileName, string $writerType = null, array $headers = [])
 * @method static bool store(object $export, string $filePath, string $disk = null, string $writerType = null, $diskOptions = [])
 * @method static PendingDispatch queue(object $export, string $filePath, string $disk = null, string $writerType = null, $diskOptions = [])
 * @method static BaseExcel import(object $import, string|UploadedFile $filePath, string $disk = null, string $readerType = null)
 * @method static array toArray(object $import, string|UploadedFile $filePath, string $disk = null, string $readerType = null)
 * @method static Collection toCollection(object $import, string|UploadedFile $filePath, string $disk = null, string $readerType = null)
 * @method static PendingDispatch queueImport(object $import, string|UploadedFile $filePath, string $disk = null, string $readerType = null)
 * @method static void matchByRegex()
 * @method static void doNotMatchByRegex()
 * @method static void assertDownloaded(string $fileName, callable $callback = null)
 * @method static void assertStored(string $filePath, string|callable $disk = null, callable $callback = null)
 * @method static void assertQueued(string $filePath, string|callable $disk = null, callable $callback = null)
 * @method static void assertQueuedWithChain(array $chain)
 * @method static void assertImported(string $filePath, string|callable $disk = null, callable $callback = null)
 */
class Excel extends Facade
{
    /**
     * Replace the bound instance with a fake.
     *
     * @return void
     */
    public static function fake()
    {
        static::swap(new ExcelFake());
    }

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
