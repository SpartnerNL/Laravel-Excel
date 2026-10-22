<?php

namespace Maatwebsite\Excel\Jobs;

use Illuminate\Bus\Batchable;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Maatwebsite\Excel\Files\Filesystem;
use Maatwebsite\Excel\Files\TemporaryFile;
use RuntimeException;

class StoreQueuedExport implements ShouldQueue
{
    use Batchable, Dispatchable, InteractsWithQueue, Queueable;

    /**
     * @param  array<string, mixed>|string  $diskOptions
     */
    public function __construct(
        private readonly TemporaryFile $temporaryFile,
        private readonly string $filePath,
        private readonly ?string $disk = null,
        private readonly array|string $diskOptions = [],
    ) {
    }

    public function handle(Filesystem $filesystem): void
    {
        // Determine if the batch has been cancelled...
        if ($this->batch()?->cancelled()) {
            return;
        }

        $disk       = $filesystem->disk($this->disk, $this->diskOptions);
        $readStream = $this->temporaryFile->readStream();

        // Without a temporary file there is nothing to store, for example when an
        // earlier attempt already stored the export and deleted it (#4305).
        if (is_resource($readStream)) {
            /** @var resource|closed-resource $readStream */
            try {
                $stored = $disk->put($this->filePath, $readStream);
            } finally {
                if (is_resource($readStream)) {
                    fclose($readStream);
                }
            }

            // Keep the temporary file, so a retry of this job can still store it.
            if (!$stored) {
                throw new RuntimeException(sprintf('Unable to store the export at [%s].', $this->filePath));
            }
        }

        $this->temporaryFile->delete();
    }
}
