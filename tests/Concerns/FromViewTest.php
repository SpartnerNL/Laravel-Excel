<?php

namespace Maatwebsite\Excel\Tests\Concerns;

use Illuminate\Contracts\View\View;
use Illuminate\Support\Collection;
use Maatwebsite\Excel\Concerns\Exportable;
use Maatwebsite\Excel\Concerns\FromView;
use Maatwebsite\Excel\Concerns\WithMultipleSheets;
use Maatwebsite\Excel\Tests\Data\Stubs\Database\User;
use Maatwebsite\Excel\Tests\Data\Stubs\SheetForUsersFromView;
use Maatwebsite\Excel\Tests\TestCase;

class FromViewTest extends TestCase
{
    /**
     * Setup the test environment.
     */
    protected function setUp(): void
    {
        parent::setUp();

        $this->withFactories(__DIR__ . '/../Data/Stubs/Database/Factories');
    }

    public function test_can_export_from_view()
    {
        /** @var Collection|User[] $users */
        $users = factory(User::class)->times(100)->make();

        $export = new class($users) implements FromView
        {
            use Exportable;

            /**
             * @var Collection
             */
            protected $users;

            /**
             * @param  Collection  $users
             */
            public function __construct(Collection $users)
            {
                $this->users = $users;
            }

            /**
             * @return View
             */
            public function view(): View
            {
                return view('users', [
                    'users' => $this->users,
                ]);
            }
        };

        $response = $export->store('from-view.xlsx');

        $this->assertTrue($response);

        $contents = $this->readAsArray(__DIR__ . '/../Data/Disks/Local/from-view.xlsx', 'Xlsx');

        $expected = $users->map(function (User $user) {
            return [
                $user->name,
                $user->email,
            ];
        })->prepend(['Name', 'Email'])->toArray();

        $this->assertEquals($expected, $contents);
    }

    public function test_can_export_multiple_sheets_from_view()
    {
        /** @var Collection|User[] $users */
        $users = factory(User::class)->times(300)->make();

        $export = new class($users) implements WithMultipleSheets
        {
            use Exportable;

            /**
             * @var Collection
             */
            protected $users;

            /**
             * @param  Collection  $users
             */
            public function __construct(Collection $users)
            {
                $this->users = $users;
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
        };

        $response = $export->store('from-multiple-view.xlsx');

        $this->assertTrue($response);

        $contents = $this->readAsArray(__DIR__ . '/../Data/Disks/Local/from-multiple-view.xlsx', 'Xlsx', 0);

        $expected = $users->forPage(1, 100)->map(function (User $user) {
            return [
                $user->name,
                $user->email,
            ];
        })->prepend(['Name', 'Email'])->toArray();

        $this->assertEquals(101, sizeof($contents));
        $this->assertEquals($expected, $contents);

        $contents = $this->readAsArray(__DIR__ . '/../Data/Disks/Local/from-multiple-view.xlsx', 'Xlsx', 2);

        $expected = $users->forPage(3, 100)->map(function (User $user) {
            return [
                $user->name,
                $user->email,
            ];
        })->prepend(['Name', 'Email'])->toArray();

        $this->assertEquals(101, sizeof($contents));
        $this->assertEquals($expected, $contents);
    }
}
