<?php

namespace Maatwebsite\Excel\Tests\Data\Stubs;

use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Support\LazyCollection;
use Maatwebsite\Excel\Concerns\Exportable;
use Maatwebsite\Excel\Concerns\FromCollection;

/**
 * @implements FromCollection<int, array{firstname: string, lastname: string}>
 */
class EloquentLazyCollectionQueuedExport implements FromCollection, ShouldQueue
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
