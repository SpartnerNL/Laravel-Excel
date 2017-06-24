<?php

namespace Maatwebsite\Excel;

interface Cell
{
    /**
     * @return string
     */
    public function __toString();

    /**
     * @return string
     */
    public function getCoordinate(): string;

    /**
     * @return string
     */
    public function getColumn(): string;

    /**
     * @return int
     */
    public function getRow(): int;

    /**
     * @return mixed
     */
    public function getValue();
}
