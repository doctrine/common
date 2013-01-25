<?php

namespace Doctrine\Tests\Common\Proxy;

use Doctrine\Tests\DoctrineTestCase;
use Doctrine\Common\Proxy\ProxyDefinition;

class AbstractProxyFactoryTest extends DoctrineTestCase
{
    public function testGenerateProxyClasses()
    {
        $metadata = $this->getMock('Doctrine\Common\Persistence\Mapping\ClassMetadata');

        $proxyGenerator = $this->getMock('Doctrine\Common\Proxy\ProxyGenerator', array(), array(), '', false);
        $proxyGenerator->expects($this->once())->method('getProxyFileName');
        $proxyGenerator->expects($this->once())->method('generateProxyClass');

        $manager = $this->getMock('Doctrine\Common\Persistence\ObjectManager');

        $proxyFactory = $this->getMock(
            'Doctrine\Common\Proxy\AbstractProxyFactory',
            array('skipClass', 'createProxyDefinition'),
            array($proxyGenerator, $manager, true)
        );

        $proxyFactory->expects($this->any())->method('skipClass')->will($this->returnValue(false));

        $generated = $proxyFactory->generateProxyClasses(array($metadata), sys_get_temp_dir());

        $this->assertEquals(1, $generated, "One proxy was generated.");
    }

    public function testGetProxy()
    {
        $metadata = $this->getMock('Doctrine\Common\Persistence\Mapping\ClassMetadata');

        $proxy = $this->getMock('Doctrine\Common\Proxy\Proxy');
        $definition = new ProxyDefinition(get_class($proxy), array(), array(), null, null);

        $proxyGenerator = $this->getMock('Doctrine\Common\Proxy\ProxyGenerator', array(), array(), '', false);
        $manager = $this->getMock('Doctrine\Common\Persistence\ObjectManager');
        $manager->expects($this->once())->method('getClassMetadata')->will($this->returnValue($metadata));

        $proxyFactory = $this->getMock(
            'Doctrine\Common\Proxy\AbstractProxyFactory',
            array('skipClass', 'createProxyDefinition'),
            array($proxyGenerator, $manager, true)
        );

        $proxyFactory->expects($this->any())->method('createProxyDefinition')->will($this->returnValue($definition));

        $generatedProxy = $proxyFactory->getProxy('Class', array('id' => 1));

        $this->assertInstanceOf(get_class($proxy), $generatedProxy);
    }

    public function testResetUnitializedProxy()
    {
        $metadata = $this->getMock('Doctrine\Common\Persistence\Mapping\ClassMetadata');
        $proxy = $this->getMock('Doctrine\Common\Proxy\Proxy');
        $definition = new ProxyDefinition(get_class($proxy), array(), array(), null, null);

        $proxyGenerator = $this->getMock('Doctrine\Common\Proxy\ProxyGenerator', array(), array(), '', false);
        $manager = $this->getMock('Doctrine\Common\Persistence\ObjectManager');
        $manager->expects($this->once())->method('getClassMetadata')->will($this->returnValue($metadata));

        $proxyFactory = $this->getMock(
            'Doctrine\Common\Proxy\AbstractProxyFactory',
            array('skipClass', 'createProxyDefinition'),
            array($proxyGenerator, $manager, true)
        );

        $proxyFactory->expects($this->any())->method('createProxyDefinition')->will($this->returnValue($definition));

        $proxy->expects($this->once())->method('__isInitialized')->will($this->returnValue(false));
        $proxy->expects($this->once())->method('__setInitializer');
        $proxy->expects($this->once())->method('__setCloner');

        $proxyFactory->resetUninitializedProxy($proxy);
    }
}

