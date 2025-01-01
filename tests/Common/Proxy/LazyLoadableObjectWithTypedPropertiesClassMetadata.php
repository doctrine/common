<?php

namespace Doctrine\Tests\Common\Proxy;

use BadMethodCallException;
use Doctrine\Persistence\Mapping\ClassMetadata;
use ReflectionClass;
use function array_keys;

/**
 * Class metadata test asset for @see LazyLoadableObjectWithTypedProperties
 */
class LazyLoadableObjectWithTypedPropertiesClassMetadata implements ClassMetadata
{
    /** @var ReflectionClass */
    protected $reflectionClass;

    /** @var array<string,bool> */
    protected $identifier = [
        'publicIdentifierField'    => true,
        'protectedIdentifierField' => true,
    ];

    /** @var array<string,bool> */
    protected $fields = [
        'publicIdentifierField'    => true,
        'protectedIdentifierField' => true,
        'publicPersistentField'    => true,
        'protectedPersistentField' => true,
    ];

    /** @var array<string,bool> */
    protected $associations = [
        'publicAssociation'        => true,
        'protectedAssociation'     => true,
    ];

    /**
     * {@inheritDoc}
     */
    public function getName(): string
    {
        return $this->getReflectionClass()->getName();
    }

    /**
     * {@inheritDoc}
     */
    public function getIdentifier(): array
    {
        return array_keys($this->identifier);
    }

    /**
     * {@inheritDoc}
     */
    public function getReflectionClass(): ReflectionClass
    {
        if ($this->reflectionClass === null) {
            $this->reflectionClass = new ReflectionClass(__NAMESPACE__ . '\LazyLoadableObjectWithTypedProperties');
        }

        return $this->reflectionClass;
    }

    /**
     * {@inheritDoc}
     */
    public function isIdentifier($fieldName): bool
    {
        return isset($this->identifier[$fieldName]);
    }

    /**
     * {@inheritDoc}
     */
    public function hasField($fieldName): bool
    {
        return isset($this->fields[$fieldName]);
    }

    /**
     * {@inheritDoc}
     */
    public function hasAssociation($fieldName): bool
    {
        return isset($this->associations[$fieldName]);
    }

    /**
     * {@inheritDoc}
     */
    public function isSingleValuedAssociation($fieldName): bool
    {
        throw new BadMethodCallException('not implemented');
    }

    /**
     * {@inheritDoc}
     */
    public function isCollectionValuedAssociation($fieldName): bool
    {
        throw new BadMethodCallException('not implemented');
    }

    /**
     * {@inheritDoc}
     */
    public function getFieldNames(): array
    {
        return array_keys($this->fields);
    }

    /**
     * {@inheritDoc}
     */
    public function getIdentifierFieldNames(): array
    {
        return $this->getIdentifier();
    }

    /**
     * {@inheritDoc}
     */
    public function getAssociationNames(): array
    {
        return array_keys($this->associations);
    }

    /**
     * {@inheritDoc}
     */
    public function getTypeOfField($fieldName): ?string
    {
        return 'string';
    }

    /**
     * {@inheritDoc}
     */
    public function getAssociationTargetClass($assocName): ?string
    {
        throw new BadMethodCallException('not implemented');
    }

    /**
     * {@inheritDoc}
     */
    public function isAssociationInverseSide($assocName): bool
    {
        throw new BadMethodCallException('not implemented');
    }

    /**
     * {@inheritDoc}
     */
    public function getAssociationMappedByTargetField($assocName): string
    {
        throw new BadMethodCallException('not implemented');
    }

    /**
     * {@inheritDoc}
     */
    public function getIdentifierValues($object): array
    {
        throw new BadMethodCallException('not implemented');
    }
}
