<?php

namespace Maatwebsite\Excel\Drivers;

use Maatwebsite\Excel\Excel;

interface Driver
{
    /**
     * @return Excel
     */
    public function build(): Excel;
}
