<?php

namespace Maatwebsite\Excel\Fakes;

use Exception;
use Illuminate\Bus\PendingBatch;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\PendingDispatch;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Bus;
use Illuminate\Support\Facades\Queue;
use Illuminate\Support\Traits\Macroable;
use Maatwebsite\Excel\Concerns\ShouldBatch;
use Maatwebsite\Excel\Exporter;
use Maatwebsite\Excel\Importer;
use PHPUnit\Framework\Assert;
use PHPUnit\Framework\ExpectationFailedException;
use Symfony\Component\HttpFoundation\BinaryFileResponse;
use Symfony\Component\HttpFoundation\File\UploadedFile;

class ExcelFake implements Exporter, Importer
{
    use Macroable;

    /**
     * @var array<string, object>
     */
    protected array $downloads = [];

    /**
     * @var array<string, array<string, object>>
     */
    protected array $stored = [];

    /**
     * @var array<string, array<string, object>>
     */
    protected array $queued = [];

    /**
     * @var array<class-string, object>
     */
    protected array $raws = [];

    /**
     * @var array<string, array<string, object>>
     */
    protected array $imported = [];

    protected bool $matchByRegex = false;

    protected ?object $job = null;

    /**
     * {@inheritdoc}
     *
     * @param  array<string, string>  $headers
     */
    public function download(object $export, string $fileName, ?string $writerType = null, array $headers = []): BinaryFileResponse
    {
        $this->downloads[$fileName] = $export;

        return new BinaryFileResponse(__DIR__ . '/fake_file');
    }

    /**
     * @param  string|null  $diskName  Fallback for usage with named properties
     */
    public function store(object $export, string $filePath, ?string $disk = null, ?string $writerType = null, mixed $diskOptions = [], ?string $diskName = null): bool|PendingDispatch|PendingBatch
    {
        if ($export instanceof ShouldQueue) {
            return $this->queue($export, $filePath, $disk ?: $diskName, $writerType);
        }

        $this->stored[$disk ?: $diskName ?: 'default'][$filePath] = $export;

        return true;
    }

    public function queue(object $export, string $filePath, ?string $disk = null, ?string $writerType = null, mixed $diskOptions = []): PendingDispatch|PendingBatch
    {
        Queue::fake();

        $this->stored[$disk ?? 'default'][$filePath] = $export;
        $this->queued[$disk ?? 'default'][$filePath] = $export;

        $this->job = new class
        {
            use Queueable;

            public function handle(): void
            {
                //
            }
        };

        Queue::push($this->job);

        // Check if the export class is batchable
        if ($export instanceof ShouldBatch) {
            return Bus::batch([$this->job]);
        }

        return new PendingDispatch($this->job);
    }

    public function raw(object $export, string $writerType): string
    {
        $this->raws[$export::class] = $export;

        return 'RAW-CONTENTS';
    }

    public function import(object $import, string|UploadedFile $file, ?string $disk = null, ?string $readerType = null): static|PendingDispatch|PendingBatch
    {
        if ($import instanceof ShouldQueue) {
            return $this->queueImport($import, $file, $disk, $readerType);
        }

        $filePath = ($file instanceof UploadedFile) ? $file->getClientOriginalName() : $file;

        $this->imported[$disk ?? 'default'][$filePath] = $import;

        return $this;
    }

    /**
     * @return array<array-key, array<int, array<array-key, mixed>>>
     */
    public function toArray(object $import, string|UploadedFile $file, ?string $disk = null, ?string $readerType = null): array
    {
        $filePath = ($file instanceof UploadedFile) ? $file->getFilename() : $file;

        $this->imported[$disk ?? 'default'][$filePath] = $import;

        return [];
    }

    /**
     * @return Collection<array-key, Collection<int, Collection<array-key, mixed>>>
     */
    public function toCollection(?object $import, string|UploadedFile $file, ?string $disk = null, ?string $readerType = null): Collection
    {
        $filePath = ($file instanceof UploadedFile) ? $file->getFilename() : $file;

        $this->imported[$disk ?? 'default'][$filePath] = $import;

        return new Collection;
    }

    public function queueImport(ShouldQueue $import, string|UploadedFile $file, ?string $disk = null, ?string $readerType = null): PendingDispatch|PendingBatch
    {
        Queue::fake();

        $filePath = ($file instanceof UploadedFile) ? $file->getFilename() : $file;

        $this->queued[$disk ?? 'default'][$filePath]   = $import;
        $this->imported[$disk ?? 'default'][$filePath] = $import;

        $this->job = new class
        {
            use Queueable;

            public function handle(): void
            {
                //
            }
        };

        Queue::push($this->job);

        // Check if the import class is batchable
        if ($import instanceof ShouldBatch) {
            return Bus::batch([$this->job]);
        }

        return new PendingDispatch($this->job);
    }

    /**
     * When asserting downloaded, stored, queued or imported, use regular expression
     * to look for a matching file path.
     */
    public function matchByRegex(): void
    {
        $this->matchByRegex = true;
    }

    /**
     * When asserting downloaded, stored, queued or imported, use regular string
     * comparison for matching file path.
     */
    public function doNotMatchByRegex(): void
    {
        $this->matchByRegex = false;
    }

    public function assertDownloaded(string $fileName, ?callable $callback = null): void
    {
        $fileName = $this->assertArrayHasKey($fileName, $this->downloads, sprintf('%s is not downloaded', $fileName));

        $callback = $callback ?: (fn (): true => true);

        Assert::assertTrue(
            $callback($this->downloads[$fileName]),
            "The file [{$fileName}] was not downloaded with the expected data."
        );
    }

    public function assertStored(string $filePath, string|callable|null $disk = null, ?callable $callback = null): void
    {
        if (is_callable($disk)) {
            $callback = $disk;
            $disk     = null;
        }

        $disk ??= 'default';
        $storedOnDisk = $this->stored[$disk] ?? [];

        $filePath = $this->assertArrayHasKey(
            $filePath,
            $storedOnDisk,
            sprintf('%s is not stored on disk %s', $filePath, $disk)
        );

        $callback = $callback ?: (fn (): true => true);

        Assert::assertTrue(
            $callback($storedOnDisk[$filePath]),
            "The file [{$filePath}] was not stored with the expected data."
        );
    }

    public function assertQueued(string $filePath, string|callable|null $disk = null, ?callable $callback = null): void
    {
        if (is_callable($disk)) {
            $callback = $disk;
            $disk     = null;
        }

        $disk ??= 'default';
        $queuedForDisk = $this->queued[$disk] ?? [];

        $filePath = $this->assertArrayHasKey(
            $filePath,
            $queuedForDisk,
            sprintf('%s is not queued for export on disk %s', $filePath, $disk)
        );

        $callback = $callback ?: (fn (): true => true);

        Assert::assertTrue(
            $callback($queuedForDisk[$filePath]),
            "The file [{$filePath}] was not stored with the expected data."
        );
    }

    /**
     * @param  array<int, object>  $chain
     */
    public function assertQueuedWithChain(array $chain): void
    {
        Queue::assertPushedWithChain($this->job::class, $chain);
    }

    public function assertExportedInRaw(string $classname, ?callable $callback = null): void
    {
        Assert::assertArrayHasKey($classname, $this->raws, sprintf('%s is not exported in raw', $classname));

        $callback = $callback ?: (fn (): true => true);

        Assert::assertTrue(
            $callback($this->raws[$classname]),
            "The [{$classname}] export was not exported in raw with the expected data."
        );
    }

    public function assertImported(string $filePath, string|callable|null $disk = null, ?callable $callback = null): void
    {
        if (is_callable($disk)) {
            $callback = $disk;
            $disk     = null;
        }

        $disk ??= 'default';
        $importedOnDisk = $this->imported[$disk] ?? [];

        $filePath = $this->assertArrayHasKey(
            $filePath,
            $importedOnDisk,
            sprintf('%s is not stored on disk %s', $filePath, $disk)
        );

        $callback = $callback ?: (fn (): true => true);

        Assert::assertTrue(
            $callback($importedOnDisk[$filePath]),
            "The file [{$filePath}] was not imported with the expected data."
        );
    }

    /**
     * Asserts that an array has a specified key and returns the key if successful.
     *
     * @see matchByRegex for more information about file path matching
     *
     * @param  array<string, object>  $disk
     *
     * @throws ExpectationFailedException
     * @throws Exception
     */
    protected function assertArrayHasKey(string $key, array $disk, string $message = ''): string
    {
        if ($this->matchByRegex) {
            $files   = array_keys($disk);
            $results = preg_grep($key, $files);
            Assert::assertGreaterThan(0, count($results), $message);
            Assert::assertCount(1, $results, "More than one result matches the file name expression '$key'.");

            return array_values($results)[0];
        }
        Assert::assertArrayHasKey($key, $disk, $message);

        return $key;
    }
}
