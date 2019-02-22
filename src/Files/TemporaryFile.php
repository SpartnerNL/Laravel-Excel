<?php

namespace Maatwebsite\Excel\Files;

abstract class TemporaryFile
{
    /**
     * @return string
     */
    abstract public function getLocalPath(): string;

    /**
     * @return bool
     */
    abstract public function exists(): bool;

    /**
     * @return bool
     */
    abstract public function delete(): bool;

    abstract public function store();
}