<?php

namespace Maatwebsite\Excel\Jobs;

use Throwable;

trait ProxyFailures
{
    /**
     * @param Throwable $e
     */
    public function failed(Throwable $e)
    {
        if (method_exists($this->export, 'failed')) {
            $this->export->failed($e);
        }
    }
}
