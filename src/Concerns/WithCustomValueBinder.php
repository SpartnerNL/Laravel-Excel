<?php

declare(strict_types=1);

namespace Maatwebsite\Excel\Concerns;

use PhpOffice\PhpSpreadsheet\Cell\IValueBinder;

interface WithCustomValueBinder extends IValueBinder
{
}
