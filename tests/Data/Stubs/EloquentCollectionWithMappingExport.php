<?php

namespace Seoperin\LaravelExcel\Tests\Data\Stubs;

use Seoperin\LaravelExcel\Concerns\Exportable;
use Seoperin\LaravelExcel\Concerns\WithMapping;
use Illuminate\Database\Eloquent\Collection;
use Seoperin\LaravelExcel\Concerns\FromCollection;
use Seoperin\LaravelExcel\Tests\Data\Stubs\Database\User;

class EloquentCollectionWithMappingExport implements FromCollection, WithMapping
{
    use Exportable;

    /**
     * @return Collection
     */
    public function collection()
    {
        return collect([
            new User([
                'firstname' => 'Patrick',
                'lastname'  => 'Brouwers',
            ]),
        ]);
    }

    /**
     * @param User $user
     *
     * @return array
     */
    public function map($user): array
    {
        return [
            $user->firstname,
            $user->lastname,
        ];
    }
}
