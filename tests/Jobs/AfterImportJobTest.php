<?php

declare(strict_types=1);

namespace Maatwebsite\Excel\Tests\Jobs;

use Carbon\CarbonImmutable;
use Exception;
use Maatwebsite\Excel\Concerns\Import;
use Maatwebsite\Excel\Concerns\Importable;
use Maatwebsite\Excel\Concerns\RegistersEventListeners;
use Maatwebsite\Excel\Concerns\WithEvents;
use Maatwebsite\Excel\Events\ImportFailed;
use Maatwebsite\Excel\Jobs\AfterImportJob;
use Maatwebsite\Excel\Reader;
use Maatwebsite\Excel\Tests\TestCase;
use Mockery;
use Throwable;

final class AfterImportJobTest extends TestCase
{
    public function test_handle_returns_early_when_batch_is_cancelled(): void
    {
        $import = new class implements Import
        {
            use Importable;
        };

        // A full mock without any stubbed methods: if handle() proceeds past
        // the cancellation check, any call to $reader will fail the test.
        $reader = Mockery::mock(Reader::class);

        $job           = new AfterImportJob($import, $reader);
        [$job, $batch] = $job->withFakeBatch(cancelledAt: CarbonImmutable::now());

        $job->handle();

        $this->assertTrue($batch->cancelled());
    }

    public function test_failed_raises_import_failed_event_when_import_uses_events(): void
    {
        $exception = new Exception('boom');

        $import = new class implements Import, WithEvents
        {
            use Importable, RegistersEventListeners;

            public bool $eventRaised = false;

            public ?Throwable $failedException = null;

            public function importFailed(ImportFailed $event): void
            {
                $this->eventRaised = true;
            }

            public function failed(Throwable $e): void
            {
                $this->failedException = $e;
            }
        };

        $job = new AfterImportJob($import, Mockery::mock(Reader::class));
        $job->failed($exception);

        $this->assertTrue($import->eventRaised);
        $this->assertSame($exception, $import->failedException);
    }

    public function test_failed_does_nothing_when_import_does_not_use_events(): void
    {
        $import = new class implements Import
        {
            use Importable;

            public ?Throwable $failedException = null;

            public function failed(Throwable $e): void
            {
                $this->failedException = $e;
            }
        };

        $job = new AfterImportJob($import, Mockery::mock(Reader::class));
        $job->failed(new Exception('boom'));

        $this->assertNotInstanceOf(Throwable::class, $import->failedException);
    }
}
