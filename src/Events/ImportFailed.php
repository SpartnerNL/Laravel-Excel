<?php

declare(strict_types=1);

namespace Maatwebsite\Excel\Events;

use Throwable;

class ImportFailed
{
    public function __construct(
        public Throwable $e,
    ) {
    }

    public function getException(): Throwable
    {
        return $this->e;
    }
}
