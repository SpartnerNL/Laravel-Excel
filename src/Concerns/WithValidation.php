<?php

declare(strict_types=1);

namespace Maatwebsite\Excel\Concerns;

interface WithValidation extends Import
{
    /**
     * @return array<array-key, mixed>
     */
    public function rules(): array;
}
