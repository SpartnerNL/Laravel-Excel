<?php

declare(strict_types=1);

namespace Maatwebsite\Excel\Tests\Data\Stubs;

use Illuminate\Database\Eloquent\Builder as EloquentBuilder;
use Maatwebsite\Excel\Columns\Column;
use Maatwebsite\Excel\Columns\Number;
use Maatwebsite\Excel\Columns\Text;
use Maatwebsite\Excel\Concerns\Exportable;
use Maatwebsite\Excel\Concerns\FromQuery;
use Maatwebsite\Excel\Concerns\WithColumns;
use Maatwebsite\Excel\Concerns\WithCustomChunkSize;
use Maatwebsite\Excel\Tests\Data\Stubs\Database\User;

class FromUsersQueryExportWithColumns implements FromQuery, WithColumns, WithCustomChunkSize
{
    use Exportable;

    /**
     * @return EloquentBuilder<User>
     */
    public function query(): EloquentBuilder
    {
        return User::query()->orderBy('id');
    }

    /**
     * @return Column[]
     */
    public function columns(): array
    {
        return [
            Number::make('ID', 'id')->autoFilter(),
            Text::make('Name', 'name')->width(30),
            Text::make('Email', 'email'),
        ];
    }

    /**
     * Small enough that a queued export spans several append jobs.
     */
    public function chunkSize(): int
    {
        return 10;
    }
}
