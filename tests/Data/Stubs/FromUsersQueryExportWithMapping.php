<?php

namespace Maatwebsite\Excel\Tests\Data\Stubs;

use Illuminate\Database\Eloquent\Builder as EloquentBuilder;
use Illuminate\Database\Eloquent\Relations\Relation;
use Illuminate\Database\Query\Builder;
use Maatwebsite\Excel\Concerns\Exportable;
use Maatwebsite\Excel\Concerns\FromQuery;
use Maatwebsite\Excel\Concerns\WithEvents;
use Maatwebsite\Excel\Concerns\WithMapping;
use Maatwebsite\Excel\Events\BeforeSheet;
use Maatwebsite\Excel\Tests\Data\Stubs\Database\User;

class FromUsersQueryExportWithMapping implements FromQuery, WithMapping, WithEvents
{
    use Exportable;

    /**
     * @return Builder|EloquentBuilder|Relation
     */
    public function query()
    {
        return User::query();
    }

    /**
     * @return array
     */
    public function registerEvents(): array
    {
        return [
            BeforeSheet::class   => function (BeforeSheet $event) {
                $event->sheet->chunkSize(10);
            },
        ];
    }

    /**
     * @param  User  $row
     * @return array
     */
    public function map($row): array
    {
        return [
            'name' => $row->name,
        ];
    }
}
