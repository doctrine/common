<?php

namespace Doctrine\Tests\Common\Proxy;

use ArrayIterator;
use Countable;
use stdClass;

/**
 * Test PHP 7 return types class.
 */
class ReturnTypesClass extends stdClass
{
    public function returnsClass() : stdClass
    {
        return new stdClass();
    }

    public function returnsScalar() : int
    {
        return 42;
    }

    /**
     * @return mixed[]
     */
    public function returnsArray() : array
    {
        return [];
    }

    public function returnsCallable() : callable
    {
        return 'intval';
    }

    public function returnsSelf() : self
    {
        return $this;
    }

    public function returnsParent() : parent
    {
        return $this;
    }

    public function returnsInterface() : Countable
    {
        return new ArrayIterator([]);
    }
}
