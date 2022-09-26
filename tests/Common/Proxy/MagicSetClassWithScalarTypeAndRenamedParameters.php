<?php

namespace Doctrine\Tests\Common\Proxy;

use BadMethodCallException;

/**
 * Test asset class
 */
class MagicSetClassWithScalarTypeAndRenamedParameters
{
    /** @var string */
    public $id = 'id';

    /** @var string */
    public $publicField = 'publicField';

    /** @var string|null */
    public $testAttribute;

    /**
     * @param string $n
     * @param mixed  $val
     *
     * @throws BadMethodCallException
     */
    public function __set($n, $val)
    {
        if ($n === 'test') {
            $this->testAttribute = $val;
        }

        if ($n === 'publicField' || $n === 'id') {
            throw new BadMethodCallException('Should never be called for "publicField" or "id"');
        }

        $this->testAttribute = $val;
    }
}
