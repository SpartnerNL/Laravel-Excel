<?php

declare(strict_types=1);

namespace Maatwebsite\Excel\Concerns;

interface ToArray
{
    /**
     * @param  array<array-key, mixed>  $array
     */
    public function array(array $array): void;
}
