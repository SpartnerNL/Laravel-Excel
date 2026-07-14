<?php

declare(strict_types=1);

namespace Maatwebsite\Excel\Events;

use Maatwebsite\Excel\Concerns\Export;
use Maatwebsite\Excel\Concerns\Import;

/**
 * @internal
 */
abstract class Event
{
    public function __construct(
        protected Export|Import|null $concernable,
    ) {
    }

    public function getConcernable(): Export|Import|null
    {
        return $this->concernable;
    }

    abstract public function getDelegate(): mixed;

    public function appliesToConcern(string $concern): bool
    {
        return $this->getConcernable() instanceof $concern;
    }
}
