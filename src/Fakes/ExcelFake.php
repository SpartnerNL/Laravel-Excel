<?php

namespace Maatwebsite\Excel\Fakes;

use Illuminate\Bus\Queueable;
use Illuminate\Foundation\Bus\PendingDispatch;
use Illuminate\Support\Facades\Queue;
use Maatwebsite\Excel\Exporter;
use PHPUnit\Framework\Assert;
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
        $this->downloads[] = $fileName;

        return new BinaryFileResponse(__DIR__ . '/fake_file');
    }

    /**
     * {@inheritdoc}
     */
    public function store($export, string $filePath, string $disk = null, string $writerType = null)
    {
        $this->stored[$disk ?? 'default'][] = $filePath;

        return true;
    }

    /**
     * {@inheritdoc}
     */
    public function queue($export, string $filePath, string $disk = null, string $writerType = null)
    {
        Queue::fake();

        $this->queued[$disk ?? 'default'][] = $filePath;

        return new PendingDispatch(new class
        {
            use Queueable;

            public function handle()
            {
                //
            }
        });
    }

    /**
     * @param string $fileName
     */
    public function assertDownloaded(string $fileName)
    {
        Assert::assertContains($fileName, $this->downloads, sprintf('%s is not downloaded', $fileName));
    }

    /**
     * @param string      $filePath
     * @param string|null $disk
     */
    public function assertStored(string $filePath, string $disk = null)
    {
        $disk         = $disk ?? 'default';
        $storedOnDisk = $this->stored[$disk] ?? [];

        Assert::assertContains($filePath, $storedOnDisk, sprintf('%s is not stored on disk %s', $filePath, $disk));
    }

    /**
     * @param string      $filePath
     * @param string|null $disk
     */
    public function assertQueued(string $filePath, string $disk = null)
    {
        $disk          = $disk ?? 'default';
        $queuedForDisk = $this->queued[$disk] ?? [];

        Assert::assertContains(
            $filePath,
            $queuedForDisk,
            sprintf('%s is not queued for export on disk %s', $filePath, $disk)
        );
    }
}
