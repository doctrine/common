<?php

namespace Doctrine\Tests\Common\Proxy;

/**
 * Test asset class
 * @author Jan Barasek <jan@barasek.com>
 */
class MagicIssetClassWithInteger
{
    /**
     * @var string
     */
    public $id = 'id';

    /**
     * @var string
     */
    public $publicField = 'publicField';

    /**
     * @param string $name
     *
     * @return int
     * @throws \BadMethodCallException
     */
    public function __isset(string $name): int
    {
        if ('test' === $name) {
            return 1;
        }

        if ('publicField' === $name || 'id' === $name) {
            throw new \BadMethodCallException('Should never be called for "publicField" or "id"');
        }

        return 0;
    }
}
