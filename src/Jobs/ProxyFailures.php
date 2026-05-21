<?php

namespace Maatwebsite\Excel\Jobs;

use Throwable;

trait ProxyFailures
{
    public function failed(Throwable $e): void
    {
        if (method_exists($this->sheetExport, 'failed')) {
            $this->sheetExport->failed($e);
        }
    }
}
