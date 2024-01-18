<?php

namespace Maatwebsite\Excel\Tests\Data\Stubs;

use Illuminate\Support\LazyCollection;
use Maatwebsite\Excel\Concerns\Exportable;
use Maatwebsite\Excel\Concerns\FromCollection;

class EloquentLazyCollectionExport implements FromCollection
{
    use Exportable;

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
