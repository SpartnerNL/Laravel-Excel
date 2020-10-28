<?php

namespace Maatwebsite\Excel\Tests\Data\Stubs;

use Illuminate\Database\Query\Builder;
use Illuminate\Support\Collection;
use Maatwebsite\Excel\Concerns\FromQuery;
use Maatwebsite\Excel\Concerns\Exportable;
use Maatwebsite\Excel\Concerns\WithCustomChunkSize;
use Maatwebsite\Excel\Tests\Data\Stubs\Database\User;

class FromUsersQueryExportWithPrepareRows implements FromQuery, WithCustomChunkSize
{
    use Exportable;

    /**
     * @return Builder
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

    /**
     * @param iterable $rows
     * @return iterable
     */
    public function prepareRows($rows)
    {
        return (new Collection($rows))->map(function ($user) {
            $user->name .= '_prepared_name';

            return $user;
        })->toArray();
    }
}
