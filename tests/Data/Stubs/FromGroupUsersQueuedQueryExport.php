<?php

namespace Seoperin\LaravelExcel\Tests\Data\Stubs;

use Illuminate\Database\Query\Builder;
use Seoperin\LaravelExcel\Concerns\FromQuery;
use Seoperin\LaravelExcel\Concerns\Exportable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Seoperin\LaravelExcel\Concerns\WithMapping;
use Seoperin\LaravelExcel\Concerns\WithCustomChunkSize;
use Seoperin\LaravelExcel\Tests\Data\Stubs\Database\Group;

class FromGroupUsersQueuedQueryExport implements FromQuery, WithCustomChunkSize, ShouldQueue, WithMapping
{
    use Exportable;

    /**
     * @return Builder
     */
    public function query()
    {
        return Group::first()->users();
    }

    /**
     * @param mixed $row
     *
     * @return array
     */
    public function map($row): array
    {
        return [
            $row->name,
            $row->email,
        ];
    }

    /**
     * @return int
     */
    public function chunkSize(): int
    {
        return 10;
    }
}
