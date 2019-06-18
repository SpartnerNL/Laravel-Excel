<?php

namespace Seoperin\LaravelExcel\Tests\Data\Stubs;

use Illuminate\Database\Query\Builder;
use Seoperin\LaravelExcel\Concerns\FromQuery;
use Seoperin\LaravelExcel\Concerns\Exportable;
use Seoperin\LaravelExcel\Concerns\WithCustomChunkSize;
use Seoperin\LaravelExcel\Tests\Data\Stubs\Database\User;

class FromUsersQueryExport implements FromQuery, WithCustomChunkSize
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
}
