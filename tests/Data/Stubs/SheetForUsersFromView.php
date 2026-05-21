<?php

namespace Maatwebsite\Excel\Tests\Data\Stubs;

use Illuminate\Contracts\View\View;
use Illuminate\Support\Collection;
use Maatwebsite\Excel\Concerns\Exportable;
use Maatwebsite\Excel\Concerns\FromView;

class SheetForUsersFromView implements FromView
{
    use Exportable;

    public function __construct(
        protected Collection $users,
    ) {
    }

    public function view(): View
    {
        return view('users', [
            'users' => $this->users,
        ]);
    }
}
