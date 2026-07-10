<?php

namespace Maatwebsite\Excel\Tests\Data\Stubs;

use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Database\Eloquent\Builder as EloquentBuilder;
use Illuminate\Database\Eloquent\Relations\Relation;
use Illuminate\Database\Query\Builder;
use Illuminate\Support\Facades\DB;
use Maatwebsite\Excel\Concerns\Exportable;
use Maatwebsite\Excel\Concerns\FromQuery;
use Maatwebsite\Excel\Concerns\WithCustomQuerySize;
use Maatwebsite\Excel\Concerns\WithMapping;
use Maatwebsite\Excel\Tests\Data\Stubs\Database\Group;

/**
 * @implements WithMapping<Group>
 */
class FromQueryWithCustomQuerySize implements FromQuery, ShouldQueue, WithCustomQuerySize, WithMapping
{
    use Exportable;

    public function query(): Builder|EloquentBuilder|Relation
    {
        return Group::with('users')
            ->join('group_user', 'groups.id', '=', 'group_user.group_id')
            ->select('groups.*', DB::raw('count(group_user.user_id) as number_of_users'))
            ->groupBy('groups.id')
            ->orderBy('number_of_users');
    }

    public function querySize(): int
    {
        return Group::has('users')->count();
    }

    public function map(mixed $row): array
    {
        return [
            $row->id,
            $row->name,
            $row->number_of_users,
        ];
    }
}
