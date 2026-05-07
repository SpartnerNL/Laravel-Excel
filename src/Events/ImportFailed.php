<?php

namespace Maatwebsite\Excel\Events;

use Throwable;

class ImportFailed
{
    /**
     * @var Throwable
     */
    public $e;

    public function __construct(Throwable $e)
    {
        $this->e = $e;
    }

    public function getException(): Throwable
    {
        return $this->e;
    }
}
