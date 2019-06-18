<?php

namespace Seoperin\LaravelExcel\Tests\Data\Stubs;

use Seoperin\LaravelExcel\Concerns\WithTitle;
use Seoperin\LaravelExcel\Concerns\Exportable;

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
