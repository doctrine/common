<?php

namespace Doctrine\Tests\Common\Proxy;

use BadMethodCallException;

/**
 * Magic class asset test with void return type for getter
 */
class MagicGetClassWithVoid
{
    /**
     * @throws BadMethodCallException
     */
    public function __get(string $name) : void
    {
        return;
    }
}
