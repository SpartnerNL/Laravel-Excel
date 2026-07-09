<?php

declare(strict_types=1);

namespace Maatwebsite\Excel\Concerns;

interface SkipsUnknownSheets
{
    public function onUnknownSheet(string|int $sheetName): void;
}
