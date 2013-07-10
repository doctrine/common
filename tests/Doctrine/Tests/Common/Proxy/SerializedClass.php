<?php

namespace Doctrine\Tests\Common\Proxy;

/**
 * Test asset class
 */
class SerializedClass
{
    /**
     * @var mixed
     */
    private $foo = 'foo';

    /**
     * @var mixed
     */
    protected $bar = 'bar';

    /**
     * @var mixed
     */
    public $baz = 'baz';

    /**
     * @param mixed $foo
     */
    public function setFoo($foo)
    {
        $this->foo = $foo;
    }

    /**
     * @return mixed|string
     */
    public function getFoo()
    {
        return $this->foo;
    }

    /**
     * @param $bar
     */
    public function setBar($bar)
    {
        $this->bar = $bar;
    }

    /**
     * @return mixed|string
     */
    public function getBar()
    {
        return $this->bar;
    }

    /**
     * @param $baz
     */
    public function setBaz($baz)
    {
        $this->baz = $baz;
    }

    /**
     * @return mixed|string
     */
    public function getBaz()
    {
        return $this->baz;
    }
}
