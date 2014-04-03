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
        $this->metadata = $this->getMock('Doctrine\Common\Persistence\Mapping\ClassMetadata');
        $this->cmf = new TestClassMetadataFactory($driver, $this->metadata);
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
        $cache->save(__NAMESPACE__. '\ChildEntity$CLASSMETADATA', array('metadata' => $metadata));

        $this->cmf->setCacheDriver($cache);

        $loadedMetadata = $this->cmf->getMetadataFor(__NAMESPACE__ . '\ChildEntity');
        $this->assertSame($loadedMetadata, $metadata);
    }

    public function testCacheGetMetadataFor()
    {
        $cache = new ArrayCache();
        $this->cmf->setCacheDriver($cache);

        $loadedMetadata = $this->cmf->getMetadataFor(__NAMESPACE__ . '\ChildEntity');

        $entry = $cache->fetch(__NAMESPACE__. '\ChildEntity$CLASSMETADATA'); 
        $this->assertSame($loadedMetadata, $entry['metadata']);
    }

    public function testGetMetadataLastModifiedAbsentClass()
    {
        $this->setExpectedException('Doctrine\Common\Persistence\Mapping\MappingException');
        $this->cmf->getMetadataLastModified(__NAMESPACE__ . '\AbsentClass');
    }

    public function testGetMetadataLastModifiedNotSupported()
    {
        $now = time();
        $lastModified = $this->cmf->getMetadataLastModified(__NAMESPACE__ . '\ChildEntity');
        $this->assertGreaterThanOrEqual($now, $lastModified);
        $this->assertLessThanOrEqual(time(), $lastModified);
    }

    public function testGetMetadataLastModified()
    {
        $driver = $this->getMock('Doctrine\Common\Persistence\Mapping\Driver\LastModifiedMappingDriver');
        $driver->expects($this->any())
            ->method('getMetadataLastModified')
            ->will($this->returnValueMap(array(
                array(__NAMESPACE__ . '\RootEntity',  5000000001),
                array(__NAMESPACE__ . '\ChildEntity', 5000000000),
            )));
        $cmf = new TestClassMetadataFactory($driver, $this->metadata);

        $this->assertEquals(5000000001, $cmf->getMetadataLastModified(__NAMESPACE__ . '\ChildEntity'));
        $this->assertEquals(5000000001, $cmf->getMetadataLastModified(__NAMESPACE__ . '\RootEntity'));

        $driver = $this->getMock('Doctrine\Common\Persistence\Mapping\Driver\LastModifiedMappingDriver');
        $driver->expects($this->any())
            ->method('getMetadataLastModified')
            ->will($this->returnValueMap(array(
                array(__NAMESPACE__ . '\RootEntity',  6000000000),
                array(__NAMESPACE__ . '\ChildEntity', 6000000001),
            )));
        $cmf = new TestClassMetadataFactory($driver, $this->metadata);

        $this->assertEquals(6000000001, $cmf->getMetadataLastModified(__NAMESPACE__ . '\ChildEntity'));
        $this->assertEquals(6000000000, $cmf->getMetadataLastModified(__NAMESPACE__ . '\RootEntity'));

        $driver = $this->getMock('Doctrine\Common\Persistence\Mapping\Driver\LastModifiedMappingDriver');
        $driver->expects($this->any())
            ->method('getMetadataLastModified')
            ->will($this->returnValueMap(array(
                array(__NAMESPACE__ . '\RootEntity',  1000),
                array(__NAMESPACE__ . '\ChildEntity', 7000000000),
            )));
        $cmf = new TestClassMetadataFactory($driver, $this->metadata);

        $this->assertEquals(7000000000, $cmf->getMetadataLastModified(__NAMESPACE__ . '\ChildEntity'));
        $this->assertEquals(filemtime(__FILE__), $cmf->getMetadataLastModified(__NAMESPACE__ . '\RootEntity'));
    }

    protected function setUpCheckMetadataLastModified(ArrayCache $cache, $rootLastModified, $childLastModified)
    {
        $driver = $this->getMock('Doctrine\Common\Persistence\Mapping\Driver\LastModifiedMappingDriver');

        $driver->expects($this->any())
            ->method('getMetadataLastModified')
            ->will($this->returnValueMap(array(
                array(__NAMESPACE__ . '\RootEntity',  $rootLastModified),
                array(__NAMESPACE__ . '\ChildEntity', $childLastModified),
            )));

        $this->cmf = new TestClassMetadataFactory($driver, clone $this->metadata);
        $this->cmf->setCacheDriver($cache);
        $this->cmf->setCheckMetadataLastModified(true);
    }

    public function testCheckMetadataLastModified()
    {
        $cache = new ArrayCache();

        $this->setUpCheckMetadataLastModified($cache, 5000000000, 5000000000);
        $this->cmf->driver->expects($this->exactly(2))->method('loadMetadataForClass');
        $loadedMetadata1 = $this->cmf->getMetadataFor(__NAMESPACE__ . '\ChildEntity');

        $this->setUpCheckMetadataLastModified($cache, 5000000001, 5000000001);
        $this->cmf->setCheckMetadataLastModified(false);
        $this->cmf->driver->expects($this->never())->method('loadMetadataForClass');
        $loadedMetadata2 = $this->cmf->getMetadataFor(__NAMESPACE__ . '\ChildEntity');
        $this->assertSame($loadedMetadata1, $loadedMetadata2);

        $this->setUpCheckMetadataLastModified($cache, 5000000001, 5000000001);
        $this->cmf->driver->expects($this->exactly(2))->method('loadMetadataForClass');
        $loadedMetadata3 = $this->cmf->getMetadataFor(__NAMESPACE__ . '\ChildEntity');
        $this->assertNotSame($loadedMetadata2, $loadedMetadata3);

        $this->setUpCheckMetadataLastModified($cache, 5000000001, 5000000001);
        $this->cmf->driver->expects($this->never())->method('loadMetadataForClass');
        $loadedMetadata4 = $this->cmf->getMetadataFor(__NAMESPACE__ . '\ChildEntity');
        $this->assertSame($loadedMetadata3, $loadedMetadata4);

        $this->setUpCheckMetadataLastModified($cache, 5000000003, 5000000001);
        $this->cmf->driver->expects($this->exactly(2))->method('loadMetadataForClass');
        $loadedMetadata5 = $this->cmf->getMetadataFor(__NAMESPACE__ . '\ChildEntity');
        $this->assertNotSame($loadedMetadata4, $loadedMetadata5);

        $this->setUpCheckMetadataLastModified($cache, 5000000003, 5000000002);
        $this->cmf->driver->expects($this->never())->method('loadMetadataForClass');
        $loadedMetadata6 = $this->cmf->getMetadataFor(__NAMESPACE__ . '\ChildEntity');
        $this->assertSame($loadedMetadata5, $loadedMetadata6);
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
