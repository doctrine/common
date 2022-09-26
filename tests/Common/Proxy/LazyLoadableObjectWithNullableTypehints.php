<?php

namespace Doctrine\Tests\Common\Proxy;

use stdClass as A;

/**
 * Test asset representing a lazy loadable object
 */
class LazyLoadableObjectWithNullableTypehints
{
    /** @var A */
    private $identifierFieldReturnClassOneLetterNullable;

    /** @var A */
    private $identifierFieldReturnClassOneLetterNullableWithSpace;

    public function getIdentifierFieldReturnClassOneLetterNullable() : ?A
    {
        return $this->identifierFieldReturnClassOneLetterNullable;
    }

    public function getIdentifierFieldReturnClassOneLetterNullableWithSpace() : ?A
    {
        return $this->identifierFieldReturnClassOneLetterNullableWithSpace;
    }
}
