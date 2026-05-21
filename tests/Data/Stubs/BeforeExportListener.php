<?php

namespace Maatwebsite\Excel\Tests\Data\Stubs;

class BeforeExportListener
{
    /**
     * @var callable
     */
    private $assertions;

    public function __construct(callable $assertions)
    {
        $this->assertions = $assertions;
    }

    public function __invoke(): void
    {
        ($this->assertions)(...func_get_args());
    }
}
