<?php

namespace Maatwebsite\Excel\Tests\Data\Stubs;

use Illuminate\Database\Eloquent\Builder as EloquentBuilder;
use Illuminate\Support\Collection;
use Maatwebsite\Excel\Concerns\Exportable;
use Maatwebsite\Excel\Concerns\FromQuery;
use Maatwebsite\Excel\Concerns\WithCustomChunkSize;
use Maatwebsite\Excel\Tests\Data\Stubs\Database\User;

class FromUsersQueryExportWithPrepareRows implements FromQuery, WithCustomChunkSize
{
    use Exportable;

    /**
     * @return EloquentBuilder<User>
     */
    public function query(): EloquentBuilder
    {
        return User::query();
    }

    public function chunkSize(): int
    {
        return 10;
    }

    /**
     * @param  iterable<array-key, User>  $rows
     * @return array<array-key, User>
     */
    public function prepareRows($rows): iterable
    {
        return (new Collection($rows))->map(function ($user) {
            $user->name .= '_prepared_name';

            return $user;
        })->toArray();
    }
}
