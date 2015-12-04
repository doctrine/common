<?php

namespace Doctrine\Tests\Common\Persistence;

use Doctrine\Common\Persistence\AbstractManagerRegistry;
use Doctrine\Common\Persistence\Mapping\ClassMetadata;
use Doctrine\Common\Persistence\Mapping\Driver\MappingDriver;
use Doctrine\Common\Persistence\ObjectManagerAware;
use Doctrine\Tests\Common\Persistence\Mapping\TestClassMetadataFactory;
use Doctrine\Tests\DoctrineTestCase;
use PHPUnit_Framework_TestCase;

/**
 * @groups DCOM-270
 * @uses Doctrine\Tests\Common\Persistence\TestObject
 */
class ManagerRegistryTest extends DoctrineTestCase
{
    /**
     * @var TestManagerRegistry
     */
    private $mr;

    /**
     * {@inheritdoc}
     */
    public function setUp()
    {
        $this->mr = new TestManagerRegistry(
            'ORM',
            ['default_connection'],
            ['default_manager'],
            'default',
            'default',
            ObjectManagerAware::class
        );
    }

    public function testGetManagerForClass()
    {
        $this->mr->getManagerForClass(TestObject::class);
    }

    public function testGetManagerForProxyInterface()
    {
        $this->assertNull($this->mr->getManagerForClass(ObjectManagerAware::class));
    }

    public function testGetManagerForInvalidClass()
    {
        $this->setExpectedException(
            'ReflectionException',
            'Class Doctrine\Tests\Common\Persistence\TestObjectInexistent does not exist'
        );

        $this->mr->getManagerForClass('prefix:TestObjectInexistent');
    }

    public function testGetManagerForAliasedClass()
    {
        $this->mr->getManagerForClass('prefix:TestObject');
    }

    public function testGetManagerForInvalidAliasedClass()
    {
        $this->setExpectedException(
            'ReflectionException',
            'Class Doctrine\Tests\Common\Persistence\TestObject:Foo does not exist'
        );

        $this->mr->getManagerForClass('prefix:TestObject:Foo');
    }
}

class TestManager extends PHPUnit_Framework_TestCase
{
    public function getMetadataFactory()
    {
        $driver   = $this->getMock(MappingDriver::class);
        $metadata = $this->getMock(ClassMetadata::class);

        return new TestClassMetadataFactory($driver, $metadata);
    }
}

class TestManagerRegistry extends AbstractManagerRegistry
{
    protected function getService($name)
    {
        return new TestManager();
    }

    /**
     * {@inheritdoc}
     */
    protected function resetService($name)
    {

    }

    public function getAliasNamespace($alias)
    {
        return __NAMESPACE__;
    }
}
