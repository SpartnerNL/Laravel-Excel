<?php

namespace Maatwebsite\Excel\Tests\Data\Stubs;

use Illuminate\Support\LazyCollection;
use Maatwebsite\Excel\Concerns\Exportable;
use Maatwebsite\Excel\Concerns\FromCollection;

/**
 * @implements FromCollection<int, array{firstname: string, lastname: string}>
 */
class EloquentLazyCollectionExport implements FromCollection
{
    use Exportable;

    /**
     * @return LazyCollection<int, array{firstname: string, lastname: string}>
     */
    public function collection(): LazyCollection
    {
        return collect([
            [
                'firstname' => 'Patrick',
                'lastname'  => 'Brouwers',
            ],
            [
                'firstname' => 'Patrick',
                'lastname'  => 'Brouwers',
            ],
            [
                'firstname' => 'Patrick',
                'lastname'  => 'Brouwers',
            ],
            [
                'firstname' => 'Patrick',
                'lastname'  => 'Brouwers',
            ],
        ])->lazy();
    }
}
