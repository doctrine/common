<?php

namespace Doctrine\Tests\Common\Persistence;

use Doctrine\Common\Persistence\AbstractManagerRegistry;
use Doctrine\Common\Persistence\Mapping\ClassMetadata;
use Doctrine\Common\Persistence\Mapping\Driver\MappingDriver;
use Doctrine\Common\Persistence\ObjectManager;
use Doctrine\Common\Persistence\ObjectManagerAware;
use Doctrine\Tests\Common\Persistence\Mapping\TestClassMetadataFactory;
use Doctrine\Tests\DoctrineTestCase;
use ReflectionException;

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
            ['default' => 'default_connection'],
            ['default' => 'default_manager'],
            'default',
            'default',
            ObjectManagerAware::class,
            $this->getManagerFactory()
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
        $this->expectException(ReflectionException::class);
        $this->expectExceptionMessage('Class Doctrine\Tests\Common\Persistence\TestObjectInexistent does not exist');

        $this->mr->getManagerForClass('prefix:TestObjectInexistent');
    }

    public function testGetManagerForAliasedClass()
    {
        $this->mr->getManagerForClass('prefix:TestObject');
    }

    public function testGetManagerForInvalidAliasedClass()
    {
        $this->expectException(ReflectionException::class);
        $this->expectExceptionMessage('Class Doctrine\Tests\Common\Persistence\TestObject:Foo does not exist');

        $this->mr->getManagerForClass('prefix:TestObject:Foo');
    }

    public function testResetManager()
    {
        $manager = $this->mr->getManager();
        $newManager = $this->mr->resetManager();

        $this->assertInstanceOf(ObjectManager::class, $newManager);
        $this->assertNotSame($manager, $newManager);
    }

    private function getManagerFactory()
    {
        return function () {
            $mock = $this->createMock(ObjectManager::class);
            $driver = $this->createMock(MappingDriver::class);
            $metadata = $this->createMock(ClassMetadata::class);
            $mock->method('getMetadataFactory')->willReturn(new TestClassMetadataFactory($driver, $metadata));

            return $mock;
        };
    }
}

class TestManagerRegistry extends AbstractManagerRegistry
{
    private $services;

    private $managerFactory;

    public function __construct($name, array $connections, array $managers, $defaultConnection, $defaultManager, $proxyInterfaceName, callable $managerFactory)
    {
        $this->managerFactory = $managerFactory;

        parent::__construct($name, $connections, $managers, $defaultConnection, $defaultManager, $proxyInterfaceName);
    }

    protected function getService($name)
    {
        if (!isset($this->services[$name])) {
            $this->services[$name] = call_user_func($this->managerFactory);
        }

        return $this->services[$name];
    }

    protected function resetService($name)
    {
        unset($this->services[$name]);
    }

    public function getAliasNamespace($alias)
    {
        return __NAMESPACE__;
    }
}
