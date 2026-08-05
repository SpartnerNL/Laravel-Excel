<?php

declare(strict_types=1);

namespace Maatwebsite\Excel\Tests\Data\Stubs;

use Maatwebsite\Excel\Concerns\Export;
use Maatwebsite\Excel\Concerns\Exportable;
use Maatwebsite\Excel\Concerns\WithTitle;

class WithTitleExport implements Export, WithTitle
{
    use Exportable;

    public function title(): string
    {
        return 'given-title';
    }
}
