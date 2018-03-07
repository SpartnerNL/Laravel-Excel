<?php

namespace Maatwebsite\Excel\Tests\Concerns;

use Illuminate\Support\Collection;
use Illuminate\Contracts\View\View;
use Maatwebsite\Excel\Tests\TestCase;
use Maatwebsite\Excel\Concerns\FromView;
use Maatwebsite\Excel\Concerns\Exportable;
use Maatwebsite\Excel\Tests\Data\Stubs\Database\User;

class FromViewTest extends TestCase
{
    /**
     * Setup the test environment.
     */
    protected function setUp()
    {
        parent::setUp();

        $this->withFactories(__DIR__ . '/../Data/Stubs/Database/Factories');
    }

    /**
     * @test
     */
    public function can_export_from_view()
    {
        /** @var Collection|User[] $users */
        $users = factory(User::class)->times(100)->make();

        $export = new class($users) implements FromView {
            use Exportable;

            /**
             * @var Collection
             */
            protected $users;

            /**
             * @param Collection $users
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
}
