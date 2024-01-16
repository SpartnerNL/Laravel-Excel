<?php

namespace Maatwebsite\Excel\Tests\Data\Stubs;

use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Database\Eloquent\Builder;
use Maatwebsite\Excel\Concerns\Exportable;
use Maatwebsite\Excel\Concerns\FromQuery;
use Maatwebsite\Excel\Concerns\WithCustomChunkSize;
use Maatwebsite\Excel\Concerns\WithEvents;
use Maatwebsite\Excel\Events\AfterChunk;
use Maatwebsite\Excel\Tests\Data\Stubs\Database\User;
use PHPUnit\Framework\Assert;

class ExportWithEventsChunks implements WithEvents, FromQuery, ShouldQueue, WithCustomChunkSize
{
    use Exportable;

    public static $calledEvent = 0;

    public function registerEvents(): array
    {
        return [
            AfterChunk::class => function (AfterChunk $event) {
                ExportWithEventsChunks::$calledEvent++;
                Assert::assertInstanceOf(ExportWithEventsChunks::class, $event->getConcernable());
            },
        ];
    }

    public function query(): Builder
    {
        return User::query();
    }

    public function chunkSize(): int
    {
        return 1;
    }
}
