<?php

namespace Maatwebsite\Excel;

interface Reader
{
    /**
     * @param string        $filePath
     * @param callable|null $callback
     *
     * @return Spreadsheet
     */
    public function load(string $filePath, callable $callback = null): Spreadsheet;

    /**
     * @param callable $spreadsheetLoader
     *
     * @return Reader
     */
    public function setLoader(callable $spreadsheetLoader): self;

    /**
     * @return callable
     */
    public function getLoader(): callable;
}
