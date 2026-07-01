<?php

namespace Maatwebsite\Excel\Tests\Data\Stubs;

use Exception;
use Illuminate\Support\Collection;
use Maatwebsite\Excel\Concerns\Exportable;
use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\WithMapping;
use Maatwebsite\Excel\Tests\Data\Stubs\Database\User;
use PHPUnit\Framework\Assert;

class QueuedExportWithFailedHook implements FromCollection, WithMapping
{
    use Exportable;

    public bool $failed = false;

    public function collection(): Collection
    {
        return collect([
            new User([
                'firstname' => 'Patrick',
                'lastname'  => 'Brouwers',
            ]),
        ]);
    }

    /**
     * @param  User  $user
     */
    public function map($user): array
    {
        throw new Exception('we expect this');
    }

    public function failed(Exception $exception): void
    {
        Assert::assertSame('we expect this', $exception->getMessage());

        app()->bind('queue-has-failed', fn () => true);
    }
}
