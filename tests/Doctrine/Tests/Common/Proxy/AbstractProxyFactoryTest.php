<?php

namespace Doctrine\Tests\Common\Proxy;

use Doctrine\Common\Proxy\AbstractProxyFactory;
use Doctrine\Common\Proxy\Exception\InvalidArgumentException;
use Doctrine\Common\Proxy\Proxy;
use Doctrine\Common\Proxy\ProxyDefinition;
use Doctrine\Common\Proxy\ProxyGenerator;
use Doctrine\Persistence\Mapping\ClassMetadata;
use Doctrine\Persistence\Mapping\ClassMetadataFactory;
use Doctrine\Tests\DoctrineTestCase;
use OutOfBoundsException;
use PHPUnit\Framework\MockObject\MockObject;
use ReflectionClass;
use function get_class;
use function interface_exists;
use function sys_get_temp_dir;

class AbstractProxyFactoryTest extends DoctrineTestCase
{
    /**
     * @return mixed[]
     *
     * @psalm-return array{mixed, mixed}
     */
    public function dataAutoGenerateValues() : array
    {
        return [
            [0, 0],
            [1, 1],
            [2, 2],
            [3, 3],
            [4, 4],
            ['2', 2],
            [true, 1],
            [false, 0],
            ['', 0],
        ];
    }

    /**
     * @param mixed $autoGenerate
     *
     * @dataProvider dataAutoGenerateValues
     */
    public function testNoExceptionIsThrownForValidIntegerAutoGenerateValues($autoGenerate, int $expected) : void
    {
        $proxyGenerator  = $this->createMock(ProxyGenerator::class);
        $metadataFactory = $this->createMock(ClassMetadataFactory::class);

        $proxyFactory = $this->getMockForAbstractClass(
            AbstractProxyFactory::class,
            [$proxyGenerator, $metadataFactory, $autoGenerate]
        );

        $class    = new ReflectionClass(AbstractProxyFactory::class);
        $property = $class->getProperty('autoGenerate');
        $property->setAccessible(true);

        self::assertSame($expected, $property->getValue($proxyFactory));
    }

    public function testInvalidAutoGenerateValueThrowsException() : void
    {
        $proxyGenerator  = $this->createMock(ProxyGenerator::class);
        $metadataFactory = $this->createMock(ClassMetadataFactory::class);

        $this->expectException(InvalidArgumentException::class);

        $this->getMockForAbstractClass(
            AbstractProxyFactory::class,
            [$proxyGenerator, $metadataFactory, 5]
        );
    }

    public function testGenerateProxyClasses()
    {
        $metadata       = $this->createMock(ClassMetadata::class);
        $proxyGenerator = $this->createMock(ProxyGenerator::class);

        $proxyGenerator
            ->expects($this->once())
            ->method('getProxyFileName');
        $proxyGenerator
            ->expects($this->once())
            ->method('generateProxyClass');

        $metadataFactory = $this->createMock(ClassMetadataFactory::class);
        /** @var MockObject&AbstractProxyFactory $proxyFactory */
        $proxyFactory = $this->getMockForAbstractClass(
            AbstractProxyFactory::class,
            [$proxyGenerator, $metadataFactory, true]
        );

        $proxyFactory
            ->expects($this->any())
            ->method('skipClass')
            ->will($this->returnValue(false));

        $generated = $proxyFactory->generateProxyClasses([$metadata], sys_get_temp_dir());

        self::assertEquals(1, $generated, 'One proxy was generated');
    }

    public function testGetProxy()
    {
        $metadata        = $this->createMock(ClassMetadata::class);
        $proxy           = $this->createMock(Proxy::class);
        $definition      = new ProxyDefinition(get_class($proxy), [], [], null, null);
        $proxyGenerator  = $this->createMock(ProxyGenerator::class);
        $metadataFactory = $this->createMock(ClassMetadataFactory::class);

        $metadataFactory
            ->expects($this->once())
            ->method('getMetadataFor')
            ->will($this->returnValue($metadata));

        /** @var MockObject&AbstractProxyFactory $proxyFactory */
        $proxyFactory = $this->getMockForAbstractClass(
            AbstractProxyFactory::class,
            [$proxyGenerator, $metadataFactory, true]
        );

        $proxyFactory
            ->expects($this->any())
            ->method('createProxyDefinition')
            ->will($this->returnValue($definition));

        $generatedProxy = $proxyFactory->getProxy('Class', ['id' => 1]);

        self::assertInstanceOf(get_class($proxy), $generatedProxy);
    }

    public function testResetUnitializedProxy()
    {
        $metadata = $this->createMock(ClassMetadata::class);
        /** @var MockObject&Proxy $proxy */
        $proxy           = $this->createMock(Proxy::class);
        $definition      = new ProxyDefinition(get_class($proxy), [], [], null, null);
        $proxyGenerator  = $this->createMock(ProxyGenerator::class);
        $metadataFactory = $this->createMock(ClassMetadataFactory::class);

        $metadataFactory
            ->expects($this->once())
            ->method('getMetadataFor')
            ->will($this->returnValue($metadata));

        /** @var MockObject&AbstractProxyFactory $proxyFactory */
        $proxyFactory = $this->getMockForAbstractClass(
            AbstractProxyFactory::class,
            [$proxyGenerator, $metadataFactory, true]
        );

        $proxyFactory
            ->expects($this->any())
            ->method('createProxyDefinition')
            ->will($this->returnValue($definition));

        $proxy
            ->expects($this->once())
            ->method('__isInitialized')
            ->will($this->returnValue(false));
        $proxy
            ->expects($this->once())
            ->method('__setInitializer');
        $proxy
            ->expects($this->once())
            ->method('__setCloner');

        $proxyFactory->resetUninitializedProxy($proxy);
    }

    public function testDisallowsResettingInitializedProxy()
    {
        /** @var AbstractProxyFactory $proxyFactory */
        $proxyFactory = $this->getMockForAbstractClass(AbstractProxyFactory::class, [], '', false);
        /** @var Proxy&MockObject $proxy */
        $proxy = $this->createMock(Proxy::class);

        $proxy
            ->expects($this->any())
            ->method('__isInitialized')
            ->will($this->returnValue(true));

        $this->expectException(InvalidArgumentException::class);

        $proxyFactory->resetUninitializedProxy($proxy);
    }

    public function testMissingPrimaryKeyValue()
    {
        $metadata        = $this->createMock(ClassMetadata::class);
        $proxy           = $this->createMock(Proxy::class);
        $definition      = new ProxyDefinition(get_class($proxy), ['missingKey'], [], null, null);
        $proxyGenerator  = $this->createMock(ProxyGenerator::class);
        $metadataFactory = $this->createMock(ClassMetadataFactory::class);

        $metadataFactory
            ->expects($this->once())
            ->method('getMetadataFor')
            ->will($this->returnValue($metadata));

        /** @var AbstractProxyFactory&MockObject $proxyFactory */
        $proxyFactory = $this->getMockForAbstractClass(
            AbstractProxyFactory::class,
            [$proxyGenerator, $metadataFactory, true]
        );

        $proxyFactory
            ->expects($this->any())
            ->method('createProxyDefinition')
            ->will($this->returnValue($definition));

        $this->expectException(OutOfBoundsException::class);

        $proxyFactory->getProxy('Class', []);
    }

    public function testGetProxyFileWhenProxyDoesNotExist() : void
    {
        $proxyFile = tempnam(sys_get_temp_dir(), 'proxy');
        unlink($proxyFile);

        $metadata        = $this->createMock(ClassMetadata::class);
        $definition      = new ProxyDefinition('MyObject1', [], [], null, null);
        $proxyGenerator  = $this->createMock(ProxyGenerator::class);
        $metadataFactory = $this->createMock(ClassMetadataFactory::class);

        $metadataFactory
            ->expects($this->once())
            ->method('getMetadataFor')
            ->willReturn($metadata);

        $metadata
            ->expects($this->once())
            ->method('getName')
            ->willReturn('MyObject1');

        $proxyGenerator
            ->expects($this->once())
            ->method('getProxyFileName')
            ->willReturn($proxyFile);

        $proxyGenerator
            ->expects($this->once())
            ->method('generateProxyClass')
            ->willReturnCallback(function() use ($proxyFile) {
                file_put_contents($proxyFile, '<?php class MyObject1 {} ');
            });

        /** @var MockObject&AbstractProxyFactory $proxyFactory */
        $proxyFactory = $this->getMockForAbstractClass(
            AbstractProxyFactory::class,
            [$proxyGenerator, $metadataFactory, AbstractProxyFactory::AUTOGENERATE_FILE_NOT_EXISTS_OR_CHANGED]
        );

        $proxyFactory
            ->method('createProxyDefinition')
            ->willReturn($definition);

        $generatedProxy = $proxyFactory->getProxy('Class', ['id' => 1]);

        self::assertInstanceOf('MyObject1', $generatedProxy);
    }

    public function testGetProxyFileWhenProxyIsOlderThanSource() : void
    {
        $proxyFile = tempnam(sys_get_temp_dir(), 'proxy');
        file_put_contents($proxyFile, '<?php class MyObject2 {} ');
        sleep(1);
        $sourceFile = tempnam(sys_get_temp_dir(), 'source');

        $metadata        = $this->createMock(ClassMetadata::class);
        $definition      = new ProxyDefinition('MyObject2', [], [], null, null);
        $proxyGenerator  = $this->createMock(ProxyGenerator::class);
        $metadataFactory = $this->createMock(ClassMetadataFactory::class);
        $reflection      = $this->createMock(ReflectionClass::class);

        $metadataFactory
            ->expects($this->once())
            ->method('getMetadataFor')
            ->willReturn($metadata);

        $metadata
            ->expects($this->once())
            ->method('getName')
            ->willReturn('MyObject2');

        $metadata
            ->expects($this->once())
            ->method('getReflectionClass')
            ->willReturn($reflection);

        $reflection
            ->expects($this->once())
            ->method('getFileName')
            ->willReturn($sourceFile);

        $proxyGenerator
            ->expects($this->once())
            ->method('getProxyFileName')
            ->willReturn($proxyFile);

        $proxyGenerator
            ->expects($this->once())
            ->method('generateProxyClass');

        /** @var MockObject&AbstractProxyFactory $proxyFactory */
        $proxyFactory = $this->getMockForAbstractClass(
            AbstractProxyFactory::class,
            [$proxyGenerator, $metadataFactory, AbstractProxyFactory::AUTOGENERATE_FILE_NOT_EXISTS_OR_CHANGED]
        );

        $proxyFactory
            ->method('createProxyDefinition')
            ->willReturn($definition);

        $generatedProxy = $proxyFactory->getProxy('Class', ['id' => 1]);

        self::assertInstanceOf('MyObject2', $generatedProxy);
    }

    public function testGetProxyFileWhenProxyIsNewerThanSource() : void
    {
        $sourceFile = tempnam(sys_get_temp_dir(), 'source');
        sleep(1);
        $proxyFile = tempnam(sys_get_temp_dir(), 'proxy');
        file_put_contents($proxyFile, '<?php class MyObject3 {} ');

        $metadata        = $this->createMock(ClassMetadata::class);
        $definition      = new ProxyDefinition('MyObject3', [], [], null, null);
        $proxyGenerator  = $this->createMock(ProxyGenerator::class);
        $metadataFactory = $this->createMock(ClassMetadataFactory::class);
        $reflection      = $this->createMock(ReflectionClass::class);

        $metadataFactory
            ->expects($this->once())
            ->method('getMetadataFor')
            ->willReturn($metadata);

        $metadata
            ->expects($this->once())
            ->method('getName')
            ->willReturn('MyObject3');

        $metadata
            ->expects($this->once())
            ->method('getReflectionClass')
            ->willReturn($reflection);

        $reflection
            ->expects($this->once())
            ->method('getFileName')
            ->willReturn($sourceFile);

        $proxyGenerator
            ->expects($this->once())
            ->method('getProxyFileName')
            ->willReturn($proxyFile);

        $proxyGenerator
            ->expects($this->never())
            ->method('generateProxyClass');

        /** @var MockObject&AbstractProxyFactory $proxyFactory */
        $proxyFactory = $this->getMockForAbstractClass(
            AbstractProxyFactory::class,
            [$proxyGenerator, $metadataFactory, AbstractProxyFactory::AUTOGENERATE_FILE_NOT_EXISTS_OR_CHANGED]
        );

        $proxyFactory
            ->method('createProxyDefinition')
            ->willReturn($definition);

        $generatedProxy = $proxyFactory->getProxy('Class', ['id' => 1]);

        self::assertInstanceOf('MyObject3', $generatedProxy);
    }
}
