<?php

namespace Maatwebsite\Excel;

class Excel
{
    /**
     * @var Writer
     */
    private $writer;

    /**
     * @var Reader
     */
    private $reader;

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
     * @return Reader
     */
    public function load(string $filepath, callable $callback = null): Reader
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
