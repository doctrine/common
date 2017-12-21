<?php
namespace Doctrine\Tests\Common\Proxy;

/**
 * Test asset representing a lazy loadable object with void method
 *
 * @since  2.7
 */
class LazyLoadableObjectWithVoid
{
    /** @var int */
    public $value = 0;

    public function incrementingAndReturningVoid() : void
    {
        $this->value++;
    }

    public function addingAndReturningVoid(int $i) : void
    {
        $this->value += $i;
    }
}
