<?php

namespace Maatwebsite\Excel;

class Excel
{
    /**
     * @var Writer
     */
    protected $writer;

    /**
     * @var Reader
     */
    protected $reader;

    /**
     * @param Writer $writer
     * @param Reader $reader
     */
    public function __construct(Writer $writer, Reader $reader)
    {
        $this->writer = $writer;
        $this->reader = $reader;
    }

    /**
     * @param string        $filepath
     * @param callable|null $callback
     *
     * @return Spreadsheet
     */
    public function load(string $filepath, callable $callback = null): Spreadsheet
    {
        return $this->reader->load($filepath, $callback);
    }

    /**
     * @param callable|null $callback
     *
     * @return Writer
     */
    public function create(callable $callback = null): Writer
    {
        return $this->writer->create($callback);
    }
}
