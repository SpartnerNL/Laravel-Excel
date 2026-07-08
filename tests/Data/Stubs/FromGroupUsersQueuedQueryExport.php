<?php

namespace Maatwebsite\Excel\Tests\Data\Stubs;

use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Database\Eloquent\Builder as EloquentBuilder;
use Illuminate\Database\Eloquent\Relations\Relation;
use Illuminate\Database\Query\Builder;
use Maatwebsite\Excel\Concerns\Exportable;
use Maatwebsite\Excel\Concerns\FromQuery;
use Maatwebsite\Excel\Concerns\WithCustomChunkSize;
use Maatwebsite\Excel\Concerns\WithMapping;
use Maatwebsite\Excel\Tests\Data\Stubs\Database\Group;
use Maatwebsite\Excel\Tests\Data\Stubs\Database\User;

/**
 * @implements WithMapping<User>
 */
class FromGroupUsersQueuedQueryExport implements FromQuery, ShouldQueue, WithCustomChunkSize, WithMapping
{
    use Exportable;

    /**
     * @return EloquentBuilder<User>
     */
    public function query(): Builder|EloquentBuilder|Relation
    {
        return Group::first()->users();
    }

    public function map(mixed $row): array
    {
        return [
            $row->name,
            $row->email,
        ];
    }

    public function chunkSize(): int
    {
        return 10;
    }
}
