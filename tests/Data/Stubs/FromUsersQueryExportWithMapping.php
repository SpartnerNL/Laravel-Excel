<?php

namespace Seoperin\LaravelExcel\Tests\Data\Stubs;

use Illuminate\Database\Query\Builder;
use Seoperin\LaravelExcel\Concerns\FromQuery;
use Seoperin\LaravelExcel\Events\BeforeSheet;
use Seoperin\LaravelExcel\Concerns\Exportable;
use Seoperin\LaravelExcel\Concerns\WithEvents;
use Seoperin\LaravelExcel\Concerns\WithMapping;
use Seoperin\LaravelExcel\Tests\Data\Stubs\Database\User;

class FromUsersQueryExportWithMapping implements FromQuery, WithMapping, WithEvents
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
     * @param User $row
     *
     * @return array
     */
    public function map($row): array
    {
        return [
            'name' => $row->name,
        ];
    }
}
