<?php

declare(strict_types=1);

namespace Maatwebsite\Excel\Concerns;

use PhpOffice\PhpSpreadsheet\Chart\Chart;

interface WithCharts extends Export
{
    /**
     * @return Chart|Chart[]
     */
    public function charts(): Chart|array;
}
