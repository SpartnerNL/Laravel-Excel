<?php

declare(strict_types=1);

namespace Maatwebsite\Excel\Concerns;

use PhpOffice\PhpSpreadsheet\Worksheet\BaseDrawing;

interface WithDrawings
{
    /**
     * @return BaseDrawing|BaseDrawing[]
     */
    public function drawings(): BaseDrawing|array;
}
