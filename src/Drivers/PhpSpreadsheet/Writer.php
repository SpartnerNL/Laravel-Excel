<?php

namespace Maatwebsite\Excel\Drivers\PhpSpreadsheet;

use Maatwebsite\Excel\Configuration;
use Maatwebsite\Excel\Writer as WriterInterface;

class Writer implements WriterInterface
{
    /**
     * @var Configuration
     */
    protected $configuration;

    /**
     * @param Configuration $configuration
     */
    public function __construct(Configuration $configuration)
    {
        $this->configuration = $configuration;
    }

    /**
     * @param callable|null $callback
     *
     * @return WriterInterface
     */
    public function create(callable $callback = null): WriterInterface
    {
        if (is_callable($callback)) {
            $callback($this);
        }

        return $this;
    }
}
