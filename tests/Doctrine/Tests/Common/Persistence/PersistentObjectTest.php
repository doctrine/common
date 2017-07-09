<?php

declare(strict_types=1);

namespace Doctrine\Tests\Common\Persistence;

use BadMethodCallException;
use Doctrine\Common\Persistence\ObjectManager;
use Doctrine\Common\Persistence\PersistentObject;
use Doctrine\Common\Persistence\Mapping\ClassMetadata;
use Doctrine\Common\Persistence\Mapping\ReflectionService;
use InvalidArgumentException;
use RuntimeException;

/**
 * @group DDC-1448
 */
class PersistentObjectTest extends \Doctrine\Tests\DoctrineTestCase
{
    private $cm;
    private $om;
    private $object;

    public function setUp(): void
    {
        $this->cm = new TestObjectMetadata;
        $this->om = $this->createMock(ObjectManager::class);
        $this->om->expects($this->any())->method('getClassMetadata')
                 ->will($this->returnValue($this->cm));
        $this->object = new TestObject;
        PersistentObject::setObjectManager($this->om);
        $this->object->injectObjectManager($this->om, $this->cm);
    }

    public function testGetObjectManager(): void
    {
        $this->assertSame($this->om, PersistentObject::getObjectManager());
    }

    public function testNonMatchingObjectManager(): void
    {
        $this->expectException(RuntimeException::class);
        $om = $this->createMock(ObjectManager::class);
        $this->object->injectObjectManager($om, $this->cm);
    }

    public function testGetField(): void
    {
        $this->assertEquals('beberlei', $this->object->getName());
    }

    public function testSetField(): void
    {
        $this->object->setName("test");
        $this->assertEquals("test", $this->object->getName());
    }

    public function testGetIdentifier(): void
    {
        $this->assertEquals(1, $this->object->getId());
    }

    public function testSetIdentifier(): void
    {
        $this->expectException(BadMethodCallException::class);
        $this->object->setId(2);
    }

    public function testSetUnknownField(): void
    {
        $this->expectException(BadMethodCallException::class);
        $this->object->setUnknown("test");
    }

    public function testGetUnknownField(): void
    {
        $this->expectException(BadMethodCallException::class);
        $this->object->getUnknown();
    }

    public function testGetToOneAssociation(): void
    {
        $this->assertNull($this->object->getParent());
    }

    public function testSetToOneAssociation(): void
    {
        $parent = new TestObject();
        $this->object->setParent($parent);
        $this->assertSame($parent, $this->object->getParent($parent));
    }

    public function testSetInvalidToOneAssociation(): void
    {
        $parent = new \stdClass();

        $this->expectException(InvalidArgumentException::class);
        $this->object->setParent($parent);
    }

    public function testSetToOneAssociationNull(): void
    {
        $parent = new TestObject();
        $this->object->setParent($parent);
        $this->object->setParent(null);
        $this->assertNull($this->object->getParent());
    }

    public function testAddToManyAssociation(): void
    {
        $child = new TestObject();
        $this->object->addChildren($child);

        $this->assertSame($this->object, $child->getParent());
        $this->assertEquals(1, count($this->object->getChildren()));

        $child = new TestObject();
        $this->object->addChildren($child);

        $this->assertEquals(2, count($this->object->getChildren()));
    }

    public function testAddInvalidToManyAssociation(): void
    {
        $this->expectException(InvalidArgumentException::class);
        $this->object->addChildren(new \stdClass());
    }

    public function testNoObjectManagerSet(): void
    {
        PersistentObject::setObjectManager(null);
        $child = new TestObject();

        $this->expectException(RuntimeException::class);
        $child->setName("test");
    }

    public function testInvalidMethod(): void
    {
        $this->expectException(BadMethodCallException::class);
        $this->object->asdf();
    }

    public function testAddInvalidCollection(): void
    {
        $this->expectException(BadMethodCallException::class);
        $this->object->addAsdf(new \stdClass());
    }
}

class TestObject extends PersistentObject
{
    protected $id = 1;
    protected $name = 'beberlei';
    protected $parent;
    protected $children;
}

class TestObjectMetadata implements ClassMetadata
{

    public function getAssociationMappedByTargetField(string $assocName): string
    {
        $assoc = ['children' => 'parent'];
        return $assoc[$assocName];
    }

    public function getAssociationNames(): array
    {
        return ['parent', 'children'];
    }

    public function getAssociationTargetClass(string $assocName): string
    {
        return __NAMESPACE__ . '\TestObject';
    }

    public function getFieldNames(): array
    {
        return ['id', 'name'];
    }

    public function getIdentifier(): array
    {
        return ['id'];
    }

    public function getName(): string
    {
        return __NAMESPACE__ . '\TestObject';
    }

    public function getReflectionClass(): \ReflectionClass
    {
        return new \ReflectionClass($this->getName());
    }

    public function getTypeOfField(string $fieldName): string
    {
        $types = ['id' => 'integer', 'name' => 'string'];
        return $types[$fieldName];
    }

    public function hasAssociation(string $fieldName): bool
    {
        return in_array($fieldName, ['parent', 'children']);
    }

    public function hasField(string $fieldName): bool
    {
        return in_array($fieldName, ['id', 'name']);
    }

    public function isAssociationInverseSide(string $assocName): bool
    {
        return ($assocName === 'children');
    }

    public function isCollectionValuedAssociation(string $fieldName): bool
    {
        return ($fieldName === 'children');
    }

    public function isIdentifier(string $fieldName): bool
    {
        return $fieldName === 'id';
    }

    public function isSingleValuedAssociation(string $fieldName): bool
    {
        return $fieldName === 'parent';
    }

    public function getIdentifierValues(object $object): array
    {

    }

    public function getIdentifierFieldNames(): array
    {

    }

    public function initializeReflection(ReflectionService $reflService): void
    {

    }

    public function wakeupReflection(ReflectionService $reflService): void
    {

    }
}
