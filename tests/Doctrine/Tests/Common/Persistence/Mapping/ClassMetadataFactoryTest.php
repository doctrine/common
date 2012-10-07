<?php

namespace Doctrine\Tests\Common\Persistence\Mapping;

use Doctrine\Tests\DoctrineTestCase;
use Doctrine\Common\Persistence\Mapping\ReflectionService;
use Doctrine\Common\Persistence\Mapping\ClassMetadata;
use Doctrine\Common\Persistence\Mapping\AbstractClassMetadataFactory;
use Doctrine\Common\Cache\ArrayCache;

class ClassMetadataFactoryTest extends DoctrineTestCase
{
    /**
     * @var TestClassMetadataFactory
     */
    private $cmf;

    public function setUp()
    {
        $driver = $this->getMock('Doctrine\Common\Persistence\Mapping\Driver\MappingDriver');
        $metadata = $this->getMock('Doctrine\Common\Persistence\Mapping\ClassMetadata');
        $this->cmf = new TestClassMetadataFactory($driver, $metadata);
    }

    public function testGetMetadataFor()
    {
        $metadata = $this->cmf->getMetadataFor('stdClass');

        $this->assertInstanceOf('Doctrine\Common\Persistence\Mapping\ClassMetadata', $metadata);
        $this->assertTrue($this->cmf->hasMetadataFor('stdClass'));
    }

    public function testGetParentMetadata()
    {
        $metadata = $this->cmf->getMetadataFor(__NAMESPACE__ . '\ChildEntity');

        $this->assertInstanceOf('Doctrine\Common\Persistence\Mapping\ClassMetadata', $metadata);
        $this->assertTrue($this->cmf->hasMetadataFor(__NAMESPACE__ . '\ChildEntity'));
        $this->assertTrue($this->cmf->hasMetadataFor(__NAMESPACE__ . '\RootEntity'));
    }

    public function testGetCachedMetadata()
    {
        $metadata = $this->getMock('Doctrine\Common\Persistence\Mapping\ClassMetadata');
        $cache = new ArrayCache();
        $cache->save(__NAMESPACE__. '\ChildEntity$CLASSMETADATA', $metadata);

        $this->cmf->setCacheDriver($cache);

        $loadedMetadata = $this->cmf->getMetadataFor(__NAMESPACE__ . '\ChildEntity');
        $this->assertSame($loadedMetadata, $metadata);
    }

    public function testCacheGetMetadataFor()
    {
        $cache = new ArrayCache();
        $this->cmf->setCacheDriver($cache);

        $loadedMetadata = $this->cmf->getMetadataFor(__NAMESPACE__ . '\ChildEntity');

        $this->assertSame($loadedMetadata, $cache->fetch(__NAMESPACE__. '\ChildEntity$CLASSMETADATA'));
    }

    public function testGetAliasedMetadata()
    {
        $loadedMetadata = $this->cmf->getMetadataFor('prefix:ChildEntity');

        $this->assertTrue($this->cmf->hasMetadataFor(__NAMESPACE__ . '\ChildEntity'));
        $this->assertTrue($this->cmf->hasMetadataFor('prefix:ChildEntity'));
    }

    /**
     * @covers Doctrine\Common\Persistence\Mapping\AbstractClassMetadataFactory::getAllMetadata
     */
    public function testGetAllMetadata()
    {
        $driver = $this->cmf->driver;
        $driver
            ->expects($this->once())
            ->method($this->equalTo('getAllClassNames'))
            ->will($this->returnValue(array(__NAMESPACE__ . '\RootEntity', __NAMESPACE__ . '\ChildEntity')))
        ;

        $metadata = $this->cmf->getAllMetadata();
        $this->assertCount(2, $metadata);
        $this->assertTrue($this->cmf->hasMetadataFor(__NAMESPACE__ . '\RootEntity'));
        $this->assertTrue($this->cmf->hasMetadataFor(__NAMESPACE__ . '\ChildEntity'));
    }

    /**
     * @covers Doctrine\Common\Persistence\Mapping\AbstractClassMetadataFactory::getAllMetadata
     */
    public function testGetAllMetadataWithFilter()
    {
        $driver = $this->cmf->driver;
        $driver
            ->expects($this->once())
            ->method($this->equalTo('getAllClassNames'))
            ->with($this->equalTo(array(__NAMESPACE__ . '\RootEntity')))
            ->will($this->returnValue(array(__NAMESPACE__ . '\RootEntity')))
        ;

        $metadata = $this->cmf->getAllMetadata(array(__NAMESPACE__ . '\RootEntity'));
        $this->assertCount(1, $metadata);
        $this->assertTrue($this->cmf->hasMetadataFor(__NAMESPACE__ . '\RootEntity'));
        $this->assertFalse($this->cmf->hasMetadataFor(__NAMESPACE__ . '\ChildEntity'));
    }
}

class TestClassMetadataFactory extends AbstractClassMetadataFactory
{
    public $driver;
    public $metadata;

    public function __construct($driver, $metadata)
    {
        $this->driver = $driver;
        $this->metadata = $metadata;
    }

    protected function doLoadMetadata($class, $parent, $rootEntityFound, array $nonSuperclassParents)
    {

    }

    protected function getFqcnFromAlias($namespaceAlias, $simpleClassName)
    {
        return __NAMESPACE__ . '\\' . $simpleClassName;
    }

    protected function initialize()
    {

    }

    protected function newClassMetadataInstance($className)
    {
        return $this->metadata;
    }

    protected function getDriver()
    {
        return $this->driver;
    }
    protected function wakeupReflection(ClassMetadata $class, ReflectionService $reflService)
    {
    }

    protected function initializeReflection(ClassMetadata $class, ReflectionService $reflService)
    {
    }

    protected function isEntity(ClassMetadata $class)
    {
        return true;
    }
}

class RootEntity
{

}

class ChildEntity extends RootEntity
{

}
