<?php

namespace Maatwebsite\Excel\Tests\Drivers;

use Countable;

trait CountableTestCase
{
    /**
     * @return Countable
     */
    abstract public function getCountable();

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
    public function is_countable()
    {
        $this->assertInstanceOf(Countable::class, $this->getCountable());
    }
}