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
        $this->setExpectedException(
            'Doctrine\Common\Persistence\Mapping\MappingException',
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
        $classMetadata = $this->getMock('Doctrine\Common\Persistence\Mapping\ClassMetadata');

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

        $this->setExpectedException('Doctrine\Common\Persistence\Mapping\MappingException');

        $this->cmf->getMetadataFor('Foo');
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
