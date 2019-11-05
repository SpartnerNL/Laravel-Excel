<?php

namespace Maatwebsite\Excel\Tests\Data\Stubs;

use Illuminate\Database\Query\Builder;
use Maatwebsite\Excel\Concerns\Exportable;
use Maatwebsite\Excel\Concerns\FromQuery;
use Maatwebsite\Excel\Concerns\WithMapping;
use Maatwebsite\Excel\Tests\Data\Stubs\Database\Group;

class FromNestedArraysQueryExport implements FromQuery, WithMapping
{
    use Exportable;

    /**
     * @return Builder
     */
    public function query()
    {
        $query = Group::with('users');

        return $query;
    }

    /**
     * @param Group $row
     *
     * @return array
     */
    public function map($row): array
    {
        $rows    = [];
        $sub_row = [$row->name, ''];
        $count   = 0;

        foreach ($row->users as $user) {
            if ($count === 0) {
                $sub_row[1] = $user['email'];
            } else {
                $sub_row = ['', $user['email']];
            }

            $rows[] = $sub_row;
            $count++;
        }

        if ($count === 0) {
            $rows[] = $sub_row;
        }

        return $rows;
    }
}
