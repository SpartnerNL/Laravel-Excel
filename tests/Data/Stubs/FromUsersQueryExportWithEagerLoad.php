<?php

namespace Maatwebsite\Excel\Tests\Data\Stubs;

use Illuminate\Database\Eloquent\Builder as EloquentBuilder;
use Illuminate\Database\Eloquent\Relations\Relation;
use Illuminate\Database\Query\Builder;
use Maatwebsite\Excel\Concerns\Exportable;
use Maatwebsite\Excel\Concerns\FromQuery;
use Maatwebsite\Excel\Concerns\WithMapping;
use Maatwebsite\Excel\Tests\Data\Stubs\Database\User;

class FromUsersQueryExportWithEagerLoad implements FromQuery, WithMapping
{
    use Exportable;

    /**
     * @return Builder|EloquentBuilder|Relation
     */
    public function query()
    {
        return User::query()->with([
            'groups' => function ($query) {
                $query->where('name', 'Group 1');
            },
        ])->withCount('groups');
    }

    /**
     * @param  mixed  $row
     * @return array
     */
    public function map($row): array
    {
        return [
            $row->name,
            $row->groups_count,
            $row->groups->implode('name', ', '),
        ];
    }
}
