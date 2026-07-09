<?php

namespace Maatwebsite\Excel\Concerns;

interface WithValidation
{
    /**
     * @return array<array-key, mixed>
     */
    public function rules(): array;
}
