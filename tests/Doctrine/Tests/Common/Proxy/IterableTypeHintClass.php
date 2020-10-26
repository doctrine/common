<?php

namespace Doctrine\Tests\Common\Proxy;

/**
 * Test PHP 7.1 iterable pseudotype
 */
class IterableTypeHintClass
{
    /**
     * @param iterable<mixed> $param
     */
    public function parameterType(iterable $param)
    {
    }

    /**
     * @return iterable<mixed>
     */
    public function returnType() : iterable
    {
        return [];
    }
}
