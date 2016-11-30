<?php

namespace Doctrine\Tests\Common\Persistence\Mapping;

use Doctrine\Common\Cache\Cache;
use Doctrine\Common\Persistence\Mapping\Driver\MappingDriver;
use Doctrine\Common\Persistence\Mapping\MappingException;
use Doctrine\Tests\DoctrineTestCase;
use Doctrine\Common\Persistence\Mapping\ReflectionService;
use Doctrine\Common\Persistence\Mapping\ClassMetadata;
use Doctrine\Common\Persistence\Mapping\AbstractClassMetadataFactory;
use Doctrine\Common\Cache\ArrayCache;

/**
 * @covers \Doctrine\Common\Persistence\Mapping\AbstractClassMetadataFactory
 */
class ClassMetadataFactoryTest extends DoctrineTestCase
{
    /**
     * @var TestClassMetadataFactory
     */
    private $cmf;

    public function setUp()
    {
        $driver = $this->createMock(MappingDriver::class);
        $metadata = $this->createMock(ClassMetadata::class);
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

        $this->assertInstanceOf(ClassMetadata::class, $metadata);
        $this->assertTrue($this->cmf->hasMetadataFor('stdClass'));
    }

    public function testGetMetadataForAbsentClass()
    {
        $this->expectException(MappingException::class);
        $this->cmf->getMetadataFor(__NAMESPACE__ . '\AbsentClass');
    }

    public function testGetParentMetadata()
    {
        $metadata = $this->cmf->getMetadataFor(ChildEntity::class);

        $this->assertInstanceOf(ClassMetadata::class, $metadata);
        $this->assertTrue($this->cmf->hasMetadataFor(ChildEntity::class));
        $this->assertTrue($this->cmf->hasMetadataFor(RootEntity::class));
    }

    public function testGetCachedMetadata()
    {
        $metadata = $this->createMock(ClassMetadata::class);
        $cache = new ArrayCache();
        $cache->save(ChildEntity::class . '$CLASSMETADATA', $metadata);

        $this->cmf->setCacheDriver($cache);

        $this->assertSame($metadata, $this->cmf->getMetadataFor(ChildEntity::class));
    }

    public function testCacheGetMetadataFor()
    {
        $cache = new ArrayCache();
        $this->cmf->setCacheDriver($cache);

        $loadedMetadata = $this->cmf->getMetadataFor(ChildEntity::class);

        $this->assertSame($loadedMetadata, $cache->fetch(ChildEntity::class. '$CLASSMETADATA'));
    }

    public function testGetAliasedMetadata()
    {
        $this->cmf->getMetadataFor('prefix:ChildEntity');

        $this->assertTrue($this->cmf->hasMetadataFor(__NAMESPACE__ . '\ChildEntity'));
        $this->assertTrue($this->cmf->hasMetadataFor('prefix:ChildEntity'));
    }

    /**
     * @group DCOM-270
     */
    public function testGetInvalidAliasedMetadata()
    {
        $this->expectException(MappingException::class);
        $this->expectExceptionMessage(
            'Class \'Doctrine\Tests\Common\Persistence\Mapping\ChildEntity:Foo\' does not exist'
        );

        $this->cmf->getMetadataFor('prefix:ChildEntity:Foo');
    }

    /**
     * @group DCOM-270
     */
    public function testClassIsTransient()
    {
        $this->assertTrue($this->cmf->isTransient('prefix:ChildEntity:Foo'));
    }

    public function testWillFallbackOnNotLoadedMetadata()
    {
        $classMetadata = $this->createMock(ClassMetadata::class);

        $this->cmf->fallbackCallback = function () use ($classMetadata) {
            return $classMetadata;
        };

        $this->cmf->metadata = null;

        $this->assertSame($classMetadata, $this->cmf->getMetadataFor('Foo'));
    }

    public function testWillFailOnFallbackFailureWithNotLoadedMetadata()
    {
        $this->cmf->fallbackCallback = function () {
            return null;
        };

        $this->cmf->metadata = null;

        $this->expectException(MappingException::class);

        $this->cmf->getMetadataFor('Foo');
    }

    /**
     * @group 717
     */
    public function testWillIgnoreCacheEntriesThatAreNotMetadataInstances()
    {
        /* @var $cacheDriver Cache|\PHPUnit_Framework_MockObject_MockObject */
        $cacheDriver = $this->createMock(Cache::class);

        $this->cmf->setCacheDriver($cacheDriver);

        $cacheDriver->expects(self::once())->method('fetch')->with('Foo$CLASSMETADATA')->willReturn(new \stdClass());

        /* @var $metadata ClassMetadata */
        $metadata = $this->createMock(ClassMetadata::class);

        $fallbackCallback = $this->getMockBuilder(\stdClass::class)->setMethods(['__invoke'])->getMock();

        $fallbackCallback->expects(self::any())->method('__invoke')->willReturn($metadata);

        $this->cmf->fallbackCallback = $fallbackCallback;

        self::assertSame($metadata, $this->cmf->getMetadataFor('Foo'));
    }
}

class TestClassMetadataFactory extends AbstractClassMetadataFactory
{
    public $driver;
    public $metadata;

    /** @var callable|null */
    public $fallbackCallback;

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

    protected function onNotFoundMetadata($className)
    {
        if (! $fallback = $this->fallbackCallback) {
            return null;
        }

        return $fallback();
    }

    public function isTransient($class)
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
