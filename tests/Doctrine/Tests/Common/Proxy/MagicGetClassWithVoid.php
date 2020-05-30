<?php

namespace Doctrine\Tests\Common\Proxy;

/**
 * Magic class asset test with void return type for getter
 */
class MagicGetClassWithVoid
{

    /**
     * @param string $name
     *
     * @return void
     * @throws \BadMethodCallException
     */
    public function __get(string $name): void
    {
        return;
    }
}
