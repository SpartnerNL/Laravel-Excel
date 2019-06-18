<?php

namespace Seoperin\LaravelExcel\Concerns;

interface WithValidation
{
    /**
     * @return array
     */
    public function rules(): array;
}
