<?php

namespace Maatwebsite\Excel\Tests\Data\Stubs;

use Maatwebsite\Excel\Concerns\Exportable;
use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Tests\Data\Stubs\Database\User;

class UsersExport implements FromCollection
{
    use Exportable;

    public function collection()
    {
        return User::all();
    }
}
