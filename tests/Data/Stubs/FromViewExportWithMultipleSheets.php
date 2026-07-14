<?php

namespace Maatwebsite\Excel\Tests\Data\Stubs;

use Illuminate\Support\Collection;
use Maatwebsite\Excel\Concerns\Export;
use Maatwebsite\Excel\Concerns\Exportable;
use Maatwebsite\Excel\Concerns\WithMultipleSheets;
use Maatwebsite\Excel\Tests\Data\Stubs\Database\User;

class FromViewExportWithMultipleSheets implements Export, WithMultipleSheets
{
    use Exportable;

    /**
     * @param  Collection<int, User>  $users
     */
    public function __construct(
        protected Collection $users,
    ) {
    }

    /**
     * @return SheetForUsersFromView[]
     */
    public function sheets(): array
    {
        return [
            new SheetForUsersFromView($this->users->forPage(1, 100)),
            new SheetForUsersFromView($this->users->forPage(2, 100)),
            new SheetForUsersFromView($this->users->forPage(3, 100)),
        ];
    }
}
