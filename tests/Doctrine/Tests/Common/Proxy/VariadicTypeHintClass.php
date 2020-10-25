<?php

namespace Doctrine\Tests\Common\Proxy;

/**
 * Test asset class
 */
class VariadicTypeHintClass
{
    /** @var mixed */
    public $types;
    /** @var mixed */
    public $foo;
    /** @var mixed */
    public $bar;
    /** @var mixed */
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
