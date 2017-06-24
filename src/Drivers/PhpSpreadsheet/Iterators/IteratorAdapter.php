<?php

namespace Maatwebsite\Excel\Drivers\PhpSpreadsheet\Iterators;

use Iterator;

abstract class IteratorAdapter implements Iterator
{
    /**
     * @return Iterator
     */
    abstract public function getIterator(): Iterator;

    /**
     * Move forward to next element.
     *
     * @link  http://php.net/manual/en/iterator.next.php
     *
     * @return void Any returned value is ignored.
     *
     * @since 5.0.0
     */
    public function next()
    {
        $this->getIterator()->next();
    }

    /**
     * Return the key of the current element.
     *
     * @link  http://php.net/manual/en/iterator.key.php
     *
     * @return mixed scalar on success, or null on failure.
     *
     * @since 5.0.0
     */
    public function key()
    {
        return $this->getIterator()->key();
    }

    /**
     * Checks if current position is valid.
     *
     * @link  http://php.net/manual/en/iterator.valid.php
     *
     * @return bool The return value will be casted to boolean and then evaluated.
     *              Returns true on success or false on failure.
     *
     * @since 5.0.0
     */
    public function valid()
    {
        return $this->getIterator()->valid();
    }

    /**
     * Rewind the Iterator to the first element.
     *
     * @link  http://php.net/manual/en/iterator.rewind.php
     *
     * @return void Any returned value is ignored.
     *
     * @since 5.0.0
     */
    public function rewind()
    {
        $this->getIterator()->rewind();
    }
}
