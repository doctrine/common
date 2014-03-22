<?php

namespace Doctrine\Tests\Common\Persistence\Mapping;

use Doctrine\Tests\DoctrineTestCase;
use Doctrine\Common\Persistence\Mapping\Driver\DefaultFileLocator;
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

    public function testGetCacheDriver()
    {
        $this->assertNull($this->cmf->getCacheDriver());
        $cache = new ArrayCache();
        $this->cmf->setCacheDriver($cache);
        $this->assertSame($cache, $this->cmf->getCacheDriver());
    }

    public function testGetMetadataFor()
    {
        $metadata = $this->cmf->getMetadataFor('stdClass');

        $this->assertInstanceOf('Doctrine\Common\Persistence\Mapping\ClassMetadata', $metadata);
        $this->assertTrue($this->cmf->hasMetadataFor('stdClass'));
    }

    public function testGetMetadataForAbsentClass()
    {
        $this->setExpectedException('Doctrine\Common\Persistence\Mapping\MappingException');
        $this->cmf->getMetadataFor(__NAMESPACE__ . '\AbsentClass');
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

    protected function setUpLastModified(ArrayCache $cache, $lastModified)
    {
        $driver = $this->getMock('Doctrine\Common\Persistence\Mapping\Driver\MappingDriver');
        $metadata = $this->getMock('Doctrine\Common\Persistence\Mapping\ClassMetadata');

        $driver->expects($this->any())
            ->method('getMetadataLastModified')
            ->with(__NAMESPACE__ . '\RootEntity')
            ->will($this->returnValue($lastModified));
        $metadata->expects($this->any())
            ->method('getLastModified')
            ->will($this->returnValue($lastModified));

        $this->cmf = new TestClassMetadataFactory($driver, $metadata);
        $this->cmf->setCacheDriver($cache);
    }

    public function testLastModified()
    {
        $cache = new ArrayCache();

        $this->setUpLastModified($cache, 1000); 
        $this->cmf->setCheckLastModified(true);
        $loadedMetadata1 = $this->cmf->getMetadataFor(__NAMESPACE__ . '\RootEntity');
        $this->assertEquals(1000, $loadedMetadata1->getLastModified());

        $this->setUpLastModified($cache, 1001);
        $this->cmf->driver->expects($this->never())->method('loadMetadataForClass');
        $loadedMetadata2 = $this->cmf->getMetadataFor(__NAMESPACE__ . '\RootEntity');
        $this->assertEquals(1000, $loadedMetadata2->getLastModified());
        $this->assertSame($loadedMetadata1, $loadedMetadata2);

        $this->setUpLastModified($cache, 1001);
        $this->cmf->driver->expects($this->once())->method('loadMetadataForClass');
        $this->cmf->setCheckLastModified(true);
        $loadedMetadata3 = $this->cmf->getMetadataFor(__NAMESPACE__ . '\RootEntity');
        $this->assertEquals(1001, $loadedMetadata3->getLastModified());
        $this->assertNotSame($loadedMetadata2, $loadedMetadata3);

        $this->setUpLastModified($cache, 1001);
        $this->cmf->driver->expects($this->never())->method('loadMetadataForClass');
        $this->cmf->setCheckLastModified(true);
        $loadedMetadata4 = $this->cmf->getMetadataFor(__NAMESPACE__ . '\RootEntity');
        $this->assertEquals(1001, $loadedMetadata3->getLastModified());
        $this->assertSame($loadedMetadata3, $loadedMetadata4);
    }

    public function testGetAliasedMetadata()
    {
        $loadedMetadata = $this->cmf->getMetadataFor('prefix:ChildEntity');

        $this->assertTrue($this->cmf->hasMetadataFor(__NAMESPACE__ . '\ChildEntity'));
        $this->assertTrue($this->cmf->hasMetadataFor('prefix:ChildEntity'));
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
        $this->driver->loadMetadataForClass($class->getName(), $class);
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
        return clone $this->metadata;
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
