<?php

namespace Maatwebsite\Excel\Concerns;

interface SkipsUnknownSheets
{
    public function onUnknownSheet(string|int $sheetName): void;
}
