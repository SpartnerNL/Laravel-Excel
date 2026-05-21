<?php

namespace Maatwebsite\Excel\Events;

/**
 * @internal
 */
abstract class Event
{
    /**
     * @param  object  $concernable
     */
    public function __construct(
        protected $concernable,
    ) {
    }

    public function getConcernable(): object
    {
        return $this->concernable;
    }

    abstract public function getDelegate(): mixed;

    public function appliesToConcern(string $concern): bool
    {
        return $this->getConcernable() instanceof $concern;
    }
}
