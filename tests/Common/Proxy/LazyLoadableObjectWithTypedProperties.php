<?php

namespace Doctrine\Tests\Common\Proxy;

use stdClass;

/**
 * Test asset representing a lazy loadable object with typed properties
 */
class LazyLoadableObjectWithTypedProperties
{
    public string $publicIdentifierField;

    protected string $protectedIdentifierField;

    public string $publicTransientField = 'publicTransientFieldValue';

    protected string $protectedTransientField = 'protectedTransientFieldValue';

    public ?string $publicPersistentField = 'publicPersistentFieldValue';

    protected string $protectedPersistentField = 'protectedPersistentFieldValue';

    public string $publicAssociation = 'publicAssociationValue';

    protected string $protectedAssociation = 'protectedAssociationValue';

    /**
     * @return string
     */
    public function getProtectedIdentifierField()
    {
        return $this->protectedIdentifierField;
    }

    /**
     * @return string
     */
    public function testInitializationTriggeringMethod()
    {
        return 'testInitializationTriggeringMethod';
    }

    /**
     * @return string
     */
    public function getProtectedAssociation()
    {
        return $this->protectedAssociation;
    }

    public function publicTypeHintedMethod(stdClass $param)
    {
    }

    public function &byRefMethod()
    {
    }

    /**
     * @param mixed $thisIsNotByRef
     * @param mixed $thisIsByRef
     */
    public function byRefParamMethod($thisIsNotByRef, &$thisIsByRef)
    {
    }
}
