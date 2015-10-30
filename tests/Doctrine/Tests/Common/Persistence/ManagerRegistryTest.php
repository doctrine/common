<?php

namespace Doctrine\Tests\Common\Persistence;

use Doctrine\Common\Persistence\AbstractManagerRegistry;
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
            array('default_connection'),
            array('default_manager'),
            'default',
            'default',
            'Doctrine\Common\Persistence\ObjectManagerAware'
        );
    }

    public function testGetManagerForClass()
    {
        $this->mr->getManagerForClass('Doctrine\Tests\Common\Persistence\TestObject');
    }

    public function testGetManagerForProxyInterface()
    {
        $this->assertNull($this->mr->getManagerForClass('Doctrine\Common\Persistence\ObjectManagerAware'));
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
        $driver = $this->getMock('Doctrine\Common\Persistence\Mapping\Driver\MappingDriver');
        $metadata = $this->getMock('Doctrine\Common\Persistence\Mapping\ClassMetadata');

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
