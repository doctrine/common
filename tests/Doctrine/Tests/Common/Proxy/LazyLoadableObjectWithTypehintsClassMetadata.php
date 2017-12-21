<?php
namespace Doctrine\Tests\Common\Proxy;

use ReflectionClass;
use Doctrine\Common\Persistence\Mapping\ClassMetadata;

/**
 * Class metadata test asset for @see LazyLoadableObject
 *
 * @author Marco Pivetta <ocramius@gmail.com>
 * @since  2.4
 */
class LazyLoadableObjectWithTypehintsClassMetadata implements ClassMetadata
{
    /**
     * @var ReflectionClass
     */
    protected $reflectionClass;

    /**
     * @var array
     */
    protected $identifier = [
        'identifierFieldNoReturnTypehint' => true,
        'identifierFieldReturnTypehintScalar' => true,
        'identifierFieldReturnClassFullyQualified' => true,
        'identifierFieldReturnClassPartialUse' => true,
        'identifierFieldReturnClassFullUse' => true,
        'identifierFieldReturnClassOneWord' => true,
        'identifierFieldReturnClassOneLetter' => true,
    ];

    /**
     * @var array
     */
    protected $fields = [
        'identifierFieldNoReturnTypehint' => true,
        'identifierFieldReturnTypehintScalar' => true,
        'identifierFieldReturnClassFullyQualified' => true,
        'identifierFieldReturnClassPartialUse' => true,
        'identifierFieldReturnClassFullUse' => true,
        'identifierFieldReturnClassOneWord' => true,
        'identifierFieldReturnClassOneLetter' => true,
    ];

    /**
     * {@inheritDoc}
     */
    public function getName()
    {
        return $this->getReflectionClass()->getName();
    }

    /**
     * {@inheritDoc}
     */
    public function getIdentifier()
    {
        return array_keys($this->identifier);
    }

    /**
     * {@inheritDoc}
     */
    public function getReflectionClass()
    {
        if (null === $this->reflectionClass) {
            $this->reflectionClass = new \ReflectionClass(__NAMESPACE__ . '\LazyLoadableObjectWithTypehints');
        }

        return $this->reflectionClass;
    }

    /**
     * {@inheritDoc}
     */
    public function isIdentifier($fieldName)
    {
        return isset($this->identifier[$fieldName]);
    }

    /**
     * {@inheritDoc}
     */
    public function hasField($fieldName)
    {
        return isset($this->fields[$fieldName]);
    }

    /**
     * {@inheritDoc}
     */
    public function hasAssociation($fieldName)
    {
        return false;
    }

    /**
     * {@inheritDoc}
     */
    public function isSingleValuedAssociation($fieldName)
    {
        throw new \BadMethodCallException('not implemented');
    }

    /**
     * {@inheritDoc}
     */
    public function isCollectionValuedAssociation($fieldName)
    {
        throw new \BadMethodCallException('not implemented');
    }

    /**
     * {@inheritDoc}
     */
    public function getFieldNames()
    {
        return array_keys($this->fields);
    }

    /**
     * {@inheritDoc}
     */
    public function getIdentifierFieldNames()
    {
        return $this->getIdentifier();
    }

    /**
     * {@inheritDoc}
     */
    public function getAssociationNames()
    {
        return [];
    }

    /**
     * {@inheritDoc}
     */
    public function getTypeOfField($fieldName)
    {
        return 'string';
    }

    /**
     * {@inheritDoc}
     */
    public function getAssociationTargetClass($assocName)
    {
        throw new \BadMethodCallException('not implemented');
    }

    /**
     * {@inheritDoc}
     */
    public function isAssociationInverseSide($assocName)
    {
        throw new \BadMethodCallException('not implemented');
    }

    /**
     * {@inheritDoc}
     */
    public function getAssociationMappedByTargetField($assocName)
    {
        throw new \BadMethodCallException('not implemented');
    }

    /**
     * {@inheritDoc}
     */
    public function getIdentifierValues($object)
    {
        throw new \BadMethodCallException('not implemented');
    }
}
