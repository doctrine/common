<?php

namespace Doctrine\Tests\Common\Proxy;

use BadMethodCallException;
use Doctrine\Persistence\Mapping\ClassMetadata;
use ReflectionClass;
use function array_keys;

class LazyLoadableObjectWithPHP82UnionAndIntersectionTypeClassMetadata implements ClassMetadata
{
    /** @var ReflectionClass */
    protected $reflectionClass;

    /** @var array<string,bool> */
    protected $identifier = [
        'identifierFieldUnionAndIntersectionType' => true,
    ];

    /** @var array<string,bool> */
    protected $fields = [
        'identifierFieldUnionAndIntersectionType' => true,
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
        if ($this->reflectionClass === null) {
            $this->reflectionClass = new ReflectionClass(__NAMESPACE__ . '\LazyLoadableObjectWithPHP82UnionAndIntersectionType');
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
        throw new BadMethodCallException('not implemented');
    }

    /**
     * {@inheritDoc}
     */
    public function isCollectionValuedAssociation($fieldName)
    {
        throw new BadMethodCallException('not implemented');
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
        throw new BadMethodCallException('not implemented');
    }

    /**
     * {@inheritDoc}
     */
    public function isAssociationInverseSide($assocName)
    {
        throw new BadMethodCallException('not implemented');
    }

    /**
     * {@inheritDoc}
     */
    public function getAssociationMappedByTargetField($assocName)
    {
        throw new BadMethodCallException('not implemented');
    }

    /**
     * {@inheritDoc}
     */
    public function getIdentifierValues($object)
    {
        throw new BadMethodCallException('not implemented');
    }
}
