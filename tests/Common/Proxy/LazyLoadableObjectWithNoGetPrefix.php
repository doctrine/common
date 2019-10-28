<?php
namespace Doctrine\Tests\Common\Proxy;

use Doctrine;

/**
 * Test asset representing a lazy loadable object
 */
class LazyLoadableObjectWithNoGetPrefix
{
    /** @var string */
    private $identifierField;

    public function identifierField() : ValueId
    {
        return new ValueId($this->identifierField);
    }

}
