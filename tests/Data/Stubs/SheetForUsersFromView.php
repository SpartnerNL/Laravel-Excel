<?php

namespace Maatwebsite\Excel\Tests\Data\Stubs;

use Illuminate\Contracts\View\View;
use Illuminate\Support\Collection;
use Maatwebsite\Excel\Concerns\Exportable;
use Maatwebsite\Excel\Concerns\FromView;
use Maatwebsite\Excel\Tests\Data\Stubs\Database\User;

class SheetForUsersFromView implements FromView
{
    use Exportable;

    /**
     * @param  Collection<int, User>  $users
     */
    public function __construct(
        protected readonly Collection $users,
    ) {
    }

    public function view(): View
    {
        return view('users', [
            'users' => $this->users,
        ]);
    }
}
