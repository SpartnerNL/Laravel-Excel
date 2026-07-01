<?php

declare(strict_types=1);

namespace Maatwebsite\Excel\Concerns;

interface ToArray
{
    public function array(array $array): void;
}
