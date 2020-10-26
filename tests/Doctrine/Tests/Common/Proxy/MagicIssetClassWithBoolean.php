<?php

namespace Doctrine\Tests\Common\Proxy;

use BadMethodCallException;

/**
 * Test asset class
 */
class MagicIssetClassWithBoolean
{
    /** @var string */
    public $id = 'id';

    /** @var string */
    public $publicField = 'publicField';

    /**
     * @throws BadMethodCallException
     */
    public function __isset(string $name) : bool
    {
        if ($name === 'test') {
            return true;
        }

        if ($name === 'publicField' || $name === 'id') {
            throw new BadMethodCallException('Should never be called for "publicField" or "id"');
        }

        return false;
    }
}
