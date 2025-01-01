<?php

namespace Doctrine\Tests\Common\Proxy;

use BadMethodCallException;
use Doctrine\Persistence\Mapping\ClassMetadata;
use ReflectionClass;

/**
 * Class metadata test asset for @see LazyLoadableObjectWithVoid
 */
class LazyLoadableObjectWithVoidClassMetadata implements ClassMetadata
{
    /** @var ReflectionClass */
    protected $reflectionClass;

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
        return [];
    }

    /**
     * {@inheritDoc}
     */
    public function getReflectionClass(): ReflectionClass
    {
        if ($this->reflectionClass === null) {
            $this->reflectionClass = new ReflectionClass(__NAMESPACE__ . '\LazyLoadableObjectWithVoid');
        }

        return $this->reflectionClass;
    }

    /**
     * {@inheritDoc}
     */
    public function isIdentifier($fieldName): bool
    {
        return false;
    }

    /**
     * {@inheritDoc}
     */
    public function hasField($fieldName): bool
    {
        return false;
    }

    /**
     * {@inheritDoc}
     */
    public function hasAssociation($fieldName): bool
    {
        return false;
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
        return [];
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
        return [];
    }

    /**
     * {@inheritDoc}
     */
    public function getTypeOfField($fieldName): ?string
    {
        return 'integer';
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
