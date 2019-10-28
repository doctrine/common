<?php
namespace Doctrine\Tests\Common\Proxy;

use Doctrine;

/**
 * Test asset representing a lazy loadable object
 */
class LazyLoadableObjectWithCustomIdType
{
    /** @var string */
    private $identifierFieldWithStaticVOConstructor;

    /** @var string */
    private $identifierFieldWithVOConstructor;

    public function getIdentifierFieldWithStaticVOConstructor() : ValueId
    {
        return ValueId::new($this->identifierFieldWithStaticVOConstructor);
    }

    public function getIdentifierFieldWithVOConstructor() : ValueId
    {
        return new ValueId($this->identifierFieldWithVOConstructor);
    }
}
