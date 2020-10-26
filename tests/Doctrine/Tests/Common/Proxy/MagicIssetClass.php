<?php

namespace Doctrine\Tests\Common\Proxy;

use BadMethodCallException;

/**
 * Test asset class
 */
class MagicIssetClass
{
    /** @var string */
    public $id = 'id';

    /** @var string */
    public $publicField = 'publicField';

    /**
     * @param string $name
     *
     * @return bool
     *
     * @throws BadMethodCallException
     */
    public function __isset($name)
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
