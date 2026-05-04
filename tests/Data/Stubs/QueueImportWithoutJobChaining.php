<?php

namespace Maatwebsite\Excel\Tests\Data\Stubs;

use Maatwebsite\Excel\Concerns\Importable;
use Maatwebsite\Excel\Concerns\ShouldQueueWithoutChain;
use Maatwebsite\Excel\Concerns\ToModel;
use Maatwebsite\Excel\Concerns\WithChunkReading;
use Maatwebsite\Excel\Concerns\WithEvents;
use Maatwebsite\Excel\Events\AfterImport;
use Maatwebsite\Excel\Events\BeforeImport;
use Maatwebsite\Excel\Reader;
use Maatwebsite\Excel\Tests\Data\Stubs\Database\User;
use PHPUnit\Framework\Assert;

class QueueImportWithoutJobChaining implements ShouldQueueWithoutChain, ToModel, WithChunkReading, WithEvents
{
    use Importable;

    public $queue;

    public $before = false;

    public $after = false;

    /**
     * @return Model|null
     */
    public function model(array $row)
    {
        return new User([
            'name'     => $row[0],
            'email'    => $row[1],
            'password' => 'secret',
        ]);
    }

    public function chunkSize(): int
    {
        return 1;
    }

    public function registerEvents(): array
    {
        return [
            BeforeImport::class => function (BeforeImport $event) {
                Assert::assertInstanceOf(Reader::class, $event->reader);
                $this->before = true;
            },
            AfterImport::class => function (AfterImport $event) {
                Assert::assertInstanceOf(Reader::class, $event->reader);
                $this->after = true;
            },
        ];
    }
}
