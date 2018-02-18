<?php

namespace Maatwebsite\Excel\Concerns;

use Maatwebsite\Excel\Sheet;

interface InteractsWithSheet
{
    /**
     * @param Sheet $sheet
     */
    public function interactWithSheet(Sheet $sheet);
}
