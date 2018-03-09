<?php

namespace Maatwebsite\Excel\Fakes;

use Illuminate\Bus\Queueable;
use PHPUnit\Framework\Assert;
use Maatwebsite\Excel\Exporter;
use Illuminate\Support\Facades\Queue;
use Illuminate\Foundation\Bus\PendingDispatch;
use Symfony\Component\HttpFoundation\BinaryFileResponse;

class ExcelFake implements Exporter
{
    /**
     * @var array
     */
    protected $downloads = [];

    /**
     * @var array
     */
    protected $stored = [];

    /**
     * @var array
     */
    protected $queued = [];

    /**
     * {@inheritdoc}
     */
    public function download($export, string $fileName, string $writerType = null)
    {
        $this->downloads[$fileName] = $export;

        return new BinaryFileResponse(__DIR__ . '/fake_file');
    }

    /**
     * {@inheritdoc}
     */
    public function store($export, string $filePath, string $disk = null, string $writerType = null)
    {
        $this->stored[$disk ?? 'default'][$filePath] = $export;

        return true;
    }

    /**
     * {@inheritdoc}
     */
    public function queue($export, string $filePath, string $disk = null, string $writerType = null)
    {
        Queue::fake();

        $this->queued[$disk ?? 'default'][$filePath] = $export;

        return new PendingDispatch(new class {
            use Queueable;

            public function handle()
            {
                //
            }
        });
    }

    /**
     * @param string        $fileName
     * @param callable|null $callback
     */
    public function assertDownloaded(string $fileName, $callback = null)
    {
        Assert::assertArrayHasKey($fileName, $this->downloads, sprintf('%s is not downloaded', $fileName));

        $callback = $callback ?: function () {
            return true;
        };

        Assert::assertTrue(
            $callback($this->downloads[$fileName]),
            "The file [{$fileName}] was not downloaded with the expected data."
        );
    }

    /**
     * @param string               $filePath
     * @param string|callable|null $disk
     * @param callable|null        $callback
     */
    public function assertStored(string $filePath, $disk = null, $callback = null)
    {
        if (is_callable($disk)) {
            $callback = $disk;
            $disk     = null;
        }

        $disk         = $disk ?? 'default';
        $storedOnDisk = $this->stored[$disk] ?? [];

        Assert::assertArrayHasKey(
            $filePath,
            $storedOnDisk,
            sprintf('%s is not stored on disk %s', $filePath, $disk)
        );

        $callback = $callback ?: function () {
            return true;
        };

        Assert::assertTrue(
            $callback($storedOnDisk[$filePath]),
            "The file [{$filePath}] was not stored with the expected data."
        );
    }

    /**
     * @param string               $filePath
     * @param string|callable|null $disk
     * @param callable|null        $callback
     */
    public function assertQueued(string $filePath, $disk = null, $callback = null)
    {
        if (is_callable($disk)) {
            $callback = $disk;
            $disk     = null;
        }

        $disk          = $disk ?? 'default';
        $queuedForDisk = $this->queued[$disk] ?? [];

        Assert::assertArrayHasKey(
            $filePath,
            $queuedForDisk,
            sprintf('%s is not queued for export on disk %s', $filePath, $disk)
        );

        $callback = $callback ?: function () {
            return true;
        };

        Assert::assertTrue(
            $callback($queuedForDisk[$filePath]),
            "The file [{$filePath}] was not stored with the expected data."
        );
    }
}
