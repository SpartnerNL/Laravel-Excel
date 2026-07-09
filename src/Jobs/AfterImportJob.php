<?php

namespace Maatwebsite\Excel\Jobs;

use Illuminate\Bus\Batchable;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Support\Collection;
use Maatwebsite\Excel\Concerns\WithEvents;
use Maatwebsite\Excel\Events\ImportFailed;
use Maatwebsite\Excel\HasEventBus;
use Maatwebsite\Excel\Reader;
use Throwable;

class AfterImportJob implements ShouldQueue
{
    use Batchable, Dispatchable, HasEventBus, InteractsWithQueue, Queueable;

    /**
     * @var iterable<array-key, string>
     */
    private iterable $dependencyIds = [];

    private int $interval = 60;

    /**
     * @param  object  $import
     */
    public function __construct(
        private $import,
        private Reader $reader,
    ) {
    }

    public function setInterval(int $interval): void
    {
        $this->interval = $interval;
    }

    /**
     * @param  Collection<int, ReadChunk>  $jobs
     */
    public function setDependencies(Collection $jobs): void
    {
        $this->dependencyIds = $jobs->map(fn (ReadChunk $job): string => $job->getUniqueId())->all();
    }

    public function handle(): void
    {
        // Determine if the batch has been cancelled...
        if ($this->batch()?->cancelled()) {
            return;
        }

        foreach ($this->dependencyIds as $id) {
            if (!ReadChunk::isComplete($id)) {
                // Until there is no jobs left to run we put this job back into the queue every minute
                // Note: this will do nothing in a SyncQueue but that's desired, because in a SyncQueue jobs run in order
                $this->release($this->interval);

                return;
            }
        }

        if ($this->import instanceof ShouldQueue && $this->import instanceof WithEvents) {
            $this->reader->clearListeners();
            $this->reader->registerListeners($this->import->registerEvents());
        }

        $this->reader->afterImport($this->import);
    }

    public function failed(Throwable $e): void
    {
        if ($this->import instanceof WithEvents) {
            $this->registerListeners($this->import->registerEvents());
            $this->raise(new ImportFailed($e));

            if (method_exists($this->import, 'failed')) {
                $this->import->failed($e);
            }
        }
    }
}
