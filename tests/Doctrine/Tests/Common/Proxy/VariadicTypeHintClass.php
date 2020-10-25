<?php

namespace Doctrine\Tests\Common\Proxy;

/**
 * Test asset class
 */
class VariadicTypeHintClass
{
    public $types;
    public $foo;
    public $bar;
    public $baz;

    public function addType(...$types)
    {
        $this->types = $types;
    }

    public function addTypeWithMultipleParameters($foo, $bar, ...$baz)
    {
        $this->foo = $foo;
        $this->bar = $bar;
        $this->baz = $baz;
    }
}
