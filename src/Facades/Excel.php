<?php

namespace Maatwebsite\Excel\Facades;

use Illuminate\Foundation\Bus\PendingDispatch;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Facade;
use Maatwebsite\Excel\Concerns\Export;
use Maatwebsite\Excel\Concerns\Import;
use Maatwebsite\Excel\Contracts\QueuedSheetSourceHandler;
use Maatwebsite\Excel\Contracts\SheetSourceHandler;
use Maatwebsite\Excel\Excel as BaseExcel;
use Maatwebsite\Excel\Fakes\ExcelFake;
use Symfony\Component\HttpFoundation\BinaryFileResponse;

/**
 * @method static BinaryFileResponse download(Export $export, string $fileName, string $writerType = null, array<string, string> $headers = [])
 * @method static bool store(Export $export, string $filePath, string $disk = null, string $writerType = null, mixed $diskOptions = [])
 * @method static PendingDispatch queue(Export $export, string $filePath, string $disk = null, string $writerType = null, mixed $diskOptions = [])
 * @method static string raw(Export $export, string $writerType)
 * @method static BaseExcel import(Import $import, string|UploadedFile $filePath, string $disk = null, string $readerType = null)
 * @method static array<array-key, array<int, array<array-key, mixed>>> toArray(Import $import, string|UploadedFile $filePath, string $disk = null, string $readerType = null)
 * @method static Collection<array-key, Collection<int, Collection<array-key, mixed>>> toCollection(?Import $import, string|UploadedFile $filePath, string $disk = null, string $readerType = null)
 * @method static PendingDispatch queueImport(Import $import, string|UploadedFile $filePath, string $disk = null, string $readerType = null)
 * @method static void registerSourceHandler(SheetSourceHandler|QueuedSheetSourceHandler|string ...$handlers)
 * @method static void matchByRegex()
 * @method static void doNotMatchByRegex()
 * @method static void assertDownloaded(string $fileName, callable $callback = null)
 * @method static void assertStored(string $filePath, string|callable $disk = null, callable $callback = null)
 * @method static void assertQueued(string $filePath, string|callable $disk = null, callable $callback = null)
 * @method static void assertQueuedWithChain(array<int, object> $chain)
 * @method static void assertExportedInRaw(string $classname, callable $callback = null)
 * @method static void assertImported(string $filePath, string|callable $disk = null, callable $callback = null)
 */
class Excel extends Facade
{
    /**
     * Replace the bound instance with a fake.
     */
    public static function fake(): void
    {
        static::swap(new ExcelFake);
    }

    /**
     * Get the registered name of the component.
     */
    protected static function getFacadeAccessor(): string
    {
        return 'excel';
    }
}
