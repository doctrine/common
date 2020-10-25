<?php

namespace Doctrine\Tests\Common\Proxy;

use Countable;
use stdClass;

/**
 * Test PHP 7 return types class.
 */
class ReturnTypesClass extends stdClass
{
    public function returnsClass() : stdClass
    {
    }

    public function returnsScalar() : int
    {
    }

    public function returnsArray() : array
    {
    }

    public function returnsCallable() : callable
    {
    }

    public function returnsSelf() : self
    {
    }

    public function returnsParent() : parent
    {
    }

    public function returnsInterface() : Countable
    {
    }
}
