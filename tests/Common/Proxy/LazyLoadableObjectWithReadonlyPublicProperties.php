<?php

namespace Doctrine\Tests\Common\Proxy;

use stdClass;

/**
 * Test asset representing a lazy loadable object
 */
class LazyLoadableObjectWithReadonlyPublicProperties
{
    /** @var string */
    readonly public string $publicIdentifierField;

    /** @var string */
    protected $protectedIdentifierField;

    /** @var string */
    public $publicTransientField = 'publicTransientFieldValue';

    /** @var string */
    protected $protectedTransientField = 'protectedTransientFieldValue';

    /** @var string */
    readonly public ?string $publicPersistentField; //  = 'publicPersistentFieldValue'

    /** @var string */
    protected $protectedPersistentField = 'protectedPersistentFieldValue';

    /** @var string */
    readonly public string $publicAssociation; // = 'publicAssociationValue'

    /** @var string */
    protected $protectedAssociation = 'protectedAssociationValue';

    public function __construct(string $publicIdentifierField, string $protectedIdentifierField, string $publicPersistentField, string $publicAssociation)
    {
        $this->publicIdentifierField = $publicIdentifierField;
        $this->protectedIdentifierField = $protectedIdentifierField;
        $this->publicPersistentField = $publicPersistentField;
        $this->publicAssociation = $publicAssociation;
    }


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
