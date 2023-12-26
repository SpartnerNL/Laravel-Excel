<?php

namespace Maatwebsite\Excel\Jobs;

use Throwable;

trait ProxyFailures
{
    /**
     * @param  Throwable  $e
     */
    public function failed(Throwable $e)
    {
        if (method_exists($this->sheetExport, 'failed')) {
            $this->sheetExport->failed($e);
        }
    }
}
