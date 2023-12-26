<?php

namespace Maatwebsite\Excel\Tests\Data\Stubs;

use Maatwebsite\Excel\Concerns\Exportable;
use Maatwebsite\Excel\Concerns\WithTitle;

class WithTitleExport implements WithTitle
{
    use Exportable;

    /**
     * @return string
     */
    public function title(): string
    {
        return 'given-title';
    }
}
