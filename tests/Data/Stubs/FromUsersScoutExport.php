<?php

namespace Maatwebsite\Excel\Tests\Data\Stubs;

use Laravel\Scout\Builder;
use Maatwebsite\Excel\Concerns\Exportable;
use Maatwebsite\Excel\Concerns\FromScout;
use Maatwebsite\Excel\Concerns\WithCustomChunkSize;
use Maatwebsite\Excel\Tests\Data\Stubs\Database\User;

class FromUsersScoutExport implements FromScout, WithCustomChunkSize
{
    use Exportable;

    /**
     * @return Builder<User>
     */
    public function scout(): Builder
    {
        return new Builder(new User, '');
    }

    public function chunkSize(): int
    {
        return 10;
    }
}
