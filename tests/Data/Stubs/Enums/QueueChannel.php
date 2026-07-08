<?php

declare(strict_types=1);

namespace Maatwebsite\Excel\Tests\Data\Stubs\Enums;

enum QueueChannel: string
{
    case EXCEL_IMPORTS = 'excel-imports';
}
