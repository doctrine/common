<?php

namespace Doctrine\Tests\Common\Proxy;

class PHP81IntersectionTypes
{
    public \Traversable&\Countable $foo;

    public function setFoo(\Traversable&\Countable $foo) : \Traversable&\Countable
    {
    }
}
