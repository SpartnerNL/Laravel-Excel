<?php

declare(strict_types=1);

namespace Maatwebsite\Excel\Concerns;

interface ToArray extends Import
{
    /**
     * @param  array<array-key, mixed>  $array
     */
    public function array(array $array): void;
}
