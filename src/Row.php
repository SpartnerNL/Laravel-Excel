<?php

namespace Maatwebsite\Excel;

interface Row
{
    /**
     * @return array
     */
    public function toArray(): array;
}