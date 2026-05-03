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
    public function __construct(protected $concernable)
    {
    }

    /**
     * @return object
     */
    public function getConcernable()
    {
        return $this->concernable;
    }

    /**
     * @return mixed
     */
    abstract public function getDelegate();

    /**
     * @param  string  $concern
     * @return bool
     */
    public function appliesToConcern(string $concern): bool
    {
        return $this->getConcernable() instanceof $concern;
    }
}
