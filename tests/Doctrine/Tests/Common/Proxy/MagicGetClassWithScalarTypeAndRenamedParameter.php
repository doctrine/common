<?php

namespace Doctrine\Tests\Common\Proxy;

use BadMethodCallException;

/**
 * Test asset class
 */
class MagicGetClassWithScalarTypeAndRenamedParameter
{
    /** @var string */
    public $id = 'id';

    /** @var string */
    public $publicField = 'publicField';

    /**
     * @throws BadMethodCallException
     */
    public function __get(string $n) : string
    {
        if ($n === 'test') {
            return 'test';
        }

        if ($n === 'publicField' || $n === 'id') {
            throw new BadMethodCallException('Should never be called for "publicField" or "id"');
        }

        return 'not defined';
    }
}
