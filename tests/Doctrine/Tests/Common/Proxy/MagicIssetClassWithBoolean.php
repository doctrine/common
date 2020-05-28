<?php

namespace Doctrine\Tests\Common\Proxy;

/**
 * Test asset class
 * @author Jan Barasek <jan@barasek.com>
 */
class MagicIssetClassWithBoolean
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
     * @return bool
     * @throws \BadMethodCallException
     */
    public function __isset(string $name): bool
    {
        if ('test' === $name) {
            return true;
        }

        if ('publicField' === $name || 'id' === $name) {
            throw new \BadMethodCallException('Should never be called for "publicField" or "id"');
        }

        return false;
    }
}
