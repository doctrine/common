<?php

namespace Doctrine\Tests\Common\Proxy;

use Doctrine\Common\Persistence\Mapping\ClassMetadata;
use Doctrine\Common\Persistence\Mapping\ClassMetadataFactory;
use Doctrine\Common\Proxy\AbstractProxyFactory;
use Doctrine\Common\Proxy\Exception\InvalidArgumentException;
use Doctrine\Common\Proxy\Proxy;
use Doctrine\Common\Proxy\ProxyGenerator;
use Doctrine\Tests\DoctrineTestCase;
use Doctrine\Common\Proxy\ProxyDefinition;
use OutOfBoundsException;

class AbstractProxyFactoryTest extends DoctrineTestCase
{
    public function testGenerateProxyClasses()
    {
        $metadata       = $this->createMock(ClassMetadata::class);
        $proxyGenerator = $this->createMock(ProxyGenerator::class, [], [], '', false);

        $proxyGenerator
            ->expects($this->once())
            ->method('getProxyFileName');
        $proxyGenerator
            ->expects($this->once())
            ->method('generateProxyClass');

        $metadataFactory = $this->createMock(ClassMetadataFactory::class);
        /* @var $proxyFactory \PHPUnit_Framework_MockObject_MockObject|AbstractProxyFactory */
        $proxyFactory    = $this->getMockForAbstractClass(
            AbstractProxyFactory::class,
            [$proxyGenerator, $metadataFactory, true]
        );

        $proxyFactory
            ->expects($this->any())
            ->method('skipClass')
            ->will($this->returnValue(false));

        $generated = $proxyFactory->generateProxyClasses([$metadata], sys_get_temp_dir());

        $this->assertEquals(1, $generated, 'One proxy was generated');
    }

    public function testGetProxy()
    {
        $metadata        = $this->createMock(ClassMetadata::class);
        $proxy           = $this->createMock(Proxy::class);
        $definition      = new ProxyDefinition(get_class($proxy), [], [], null, null);
        $proxyGenerator  = $this->createMock(ProxyGenerator::class, [], [], '', false);
        $metadataFactory = $this->createMock(ClassMetadataFactory::class);

        $metadataFactory
            ->expects($this->once())
            ->method('getMetadataFor')
            ->will($this->returnValue($metadata));

        /* @var $proxyFactory \PHPUnit_Framework_MockObject_MockObject|AbstractProxyFactory */
        $proxyFactory = $this->getMockForAbstractClass(
            AbstractProxyFactory::class,
            [$proxyGenerator, $metadataFactory, true]
        );

        $proxyFactory
            ->expects($this->any())
            ->method('createProxyDefinition')
            ->will($this->returnValue($definition));

        $generatedProxy = $proxyFactory->getProxy('Class', ['id' => 1]);

        $this->assertInstanceOf(get_class($proxy), $generatedProxy);
    }

    public function testResetUnitializedProxy()
    {
        $metadata        = $this->createMock(ClassMetadata::class);
        /* @var $proxy \PHPUnit_Framework_MockObject_MockObject|Proxy */
        $proxy           = $this->createMock(Proxy::class);
        $definition      = new ProxyDefinition(get_class($proxy), [], [], null, null);
        $proxyGenerator  = $this->createMock(ProxyGenerator::class, [], [], '', false);
        $metadataFactory = $this->createMock(ClassMetadataFactory::class);

        $metadataFactory
            ->expects($this->once())
            ->method('getMetadataFor')
            ->will($this->returnValue($metadata));

        /* @var $proxyFactory \PHPUnit_Framework_MockObject_MockObject|AbstractProxyFactory */
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
        /* @var $proxyFactory AbstractProxyFactory */
        $proxyFactory = $this->getMockForAbstractClass(AbstractProxyFactory::class,  [], '', false);
        /* @var $proxy Proxy|\PHPUnit_Framework_MockObject_MockObject */
        $proxy        = $this->createMock(Proxy::class);

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
        $proxyGenerator  = $this->createMock(ProxyGenerator::class, [], [], '', false);
        $metadataFactory = $this->createMock(ClassMetadataFactory::class);

        $metadataFactory
            ->expects($this->once())
            ->method('getMetadataFor')
            ->will($this->returnValue($metadata));

        /* @var $proxyFactory AbstractProxyFactory|\PHPUnit_Framework_MockObject_MockObject */
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
}

