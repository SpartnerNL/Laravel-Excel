<?php

namespace Maatwebsite\Excel\Tests\Drivers;

use IteratorAggregate;
use Traversable;

trait IterableTestCase
{
    /**
     * @return IteratorAggregate
     */
    abstract public function getIterable();

    /**
     * Asserts that a variable is of a given type.
     *
     * @param string $expected
     * @param mixed  $actual
     * @param string $message
     */
    abstract public static function assertInstanceOf($expected, $actual, $message = '');

    /**
     * @test
     */
    public function is_iterator_aggregate()
    {
        $this->assertInstanceOf(IteratorAggregate::class, $this->getIterable());
    }

    /**
     * @test
     */
    public function is_traversable()
    {
        $this->assertInstanceOf(Traversable::class, $this->getIterable());
    }
}