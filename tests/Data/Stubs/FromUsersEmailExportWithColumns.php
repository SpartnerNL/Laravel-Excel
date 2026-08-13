<?php

declare(strict_types=1);

namespace Maatwebsite\Excel\Tests\Data\Stubs;

use Illuminate\Database\Eloquent\Builder as EloquentBuilder;
use Maatwebsite\Excel\Columns\Text;
use Maatwebsite\Excel\Concerns\Exportable;
use Maatwebsite\Excel\Concerns\FromQuery;
use Maatwebsite\Excel\Concerns\WithColumns;
use Maatwebsite\Excel\Concerns\WithCustomChunkSize;
use Maatwebsite\Excel\Tests\Data\Stubs\Database\User;

class FromUsersEmailExportWithColumns implements FromQuery, WithColumns, WithCustomChunkSize
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
     * @return Text[]
     */
    public function columns(): array
    {
        return [
            Text::make('Email', 'email'),
        ];
    }

    public function chunkSize(): int
    {
        return 10;
    }
}
