<?php

namespace Maatwebsite\Excel;

interface Writer
{
    /**
     * @param callable|null $callback
     *
     * @return Writer
     */
    public function create(callable $callback = null): self;
}
