<?php

namespace Maatwebsite\Excel\Concerns;

interface WithValidation
{
    /**
     * @param array $rows
     * @return array
     */
    public function rules(array $rows = []): array;
}
