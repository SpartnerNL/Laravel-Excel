<?php

namespace Maatwebsite\Excel\Tests\Data\Stubs;

use Illuminate\Database\Eloquent\Builder as EloquentBuilder;
use Illuminate\Database\Eloquent\Relations\Relation;
use Illuminate\Database\Query\Builder;
use Maatwebsite\Excel\Concerns\Exportable;
use Maatwebsite\Excel\Concerns\FromQuery;
use Maatwebsite\Excel\Concerns\WithCustomChunkSize;
use Maatwebsite\Excel\Concerns\WithEvents;
use Maatwebsite\Excel\Events\AfterChunk;
use Maatwebsite\Excel\Events\BeforeChunk;
use Maatwebsite\Excel\Jobs\ExtendedQueueable;
use Maatwebsite\Excel\Tests\Data\Stubs\Database\Group;
use Maatwebsite\Excel\Tests\Data\Stubs\Database\User;

class QueuedExportWithChunkEvents implements WithEvents, WithCustomChunkSize, FromQuery
{
    use Exportable, ExtendedQueueable;

    /**
     * @return array
     */
    public function registerEvents(): array
    {
        return [
            BeforeChunk::class  => function (BeforeChunk $event) {
                (new Group([
                    'name' => 'before',
                ]))->save();
            },
            AfterChunk::class => function (AfterChunk $event) {
                (new Group([
                    'name' => 'after',
                ]))->save();
            },
        ];
    }

    /**
     * @return Builder|EloquentBuilder|Relation
     */
    public function query()
    {
        return User::query();
    }

    /**
     * @return int
     */
    public function chunkSize(): int
    {
        return 10;
    }
}