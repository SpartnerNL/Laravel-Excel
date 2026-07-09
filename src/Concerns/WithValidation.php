<?php

declare(strict_types=1);

namespace Maatwebsite\Excel\Concerns;

interface WithValidation
{
    /**
     * @return array<array-key, mixed>
     */
    public function rules(): array;
}
