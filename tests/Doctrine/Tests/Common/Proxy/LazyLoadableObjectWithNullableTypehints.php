<?php
namespace Doctrine\Tests\Common\Proxy;

use Doctrine;
use stdClass as A;

/**
 * Test asset representing a lazy loadable object
 *
 * @author Marco Pivetta <ocramius@gmail.com>
 * @since  2.4
 */
class LazyLoadableObjectWithNullableTypehints
{

    /** @var \stdClass */
    private $identifierFieldReturnClassOneLetterNullable;

    /** @var \stdClass */
    private $identifierFieldReturnClassOneLetterNullableWithSpace;

    public function getIdentifierFieldReturnClassOneLetterNullable() : ?A
    {
        return $this->identifierFieldReturnClassOneLetterNullable;
    }

    public function getIdentifierFieldReturnClassOneLetterNullableWithSpace() : ? A
    {
        return $this->identifierFieldReturnClassOneLetterNullableWithSpace;
    }
}
