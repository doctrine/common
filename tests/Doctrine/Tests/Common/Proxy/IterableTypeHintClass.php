<?php

namespace Doctrine\Tests\Common\Proxy;

/**
 * Test PHP 7.1 iterable pseudotype
 */
class IterableTypeHintClass
{
    public function parameterType(iterable $param)
    {
    }

    public function returnType() : iterable
    {
    }
}
