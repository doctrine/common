<?php
namespace Doctrine\Tests\Common\Proxy;

/**
 * Test asset representing a lazy loadable object
 *
 * @author Marco Pivetta <ocramius@gmail.com>
 * @since  2.4
 */
class LazyLoadableObject
{
    /**
     * @var string
     */
    public $publicIdentifierField;

    /**
     * @var string
     */
    protected $protectedIdentifierField;

    /**
     * @var string
     */
    public $publicTransientField = 'publicTransientFieldValue';

    /**
     * @var string
     */
    protected $protectedTransientField = 'protectedTransientFieldValue';

    /**
     * @var string
     */
    public $publicPersistentField = 'publicPersistentFieldValue';

    /**
     * @var string
     */
    protected $protectedPersistentField = 'protectedPersistentFieldValue';

    /**
     * @var string
     */
    public $publicAssociation = 'publicAssociationValue';

    /**
     * @var string
     */
    protected $protectedAssociation = 'protectedAssociationValue';

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

    /**
     * @param \stdClass $param
     */
    public function publicTypeHintedMethod(\stdClass $param)
    {
    }

    /**
     *
     */
    public function &byRefMethod()
    {
    }

    /**
     * @param mixed $thisIsNotByRef
     * @param &mixed $thisIsByRef
     */
    public function byRefParamMethod($thisIsNotByRef, &$thisIsByRef)
    {
    }
}
