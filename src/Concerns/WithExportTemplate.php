<?php

declare(strict_types=1);

namespace Maatwebsite\Excel\Concerns;

use PhpOffice\PhpSpreadsheet\Spreadsheet;

interface WithExportTemplate
{
    public function exportTemplate(): Spreadsheet;
}
