<?php

namespace Doctrine\Tests\Common\Proxy;

/**
 * Test asset class
 */
class SleepClass
{
    /** @var mixed */
    public $id;

    /**
     * @return string[]
     */
    public function __sleep()
    {
        return ['id'];
    }
}
