<?php

namespace Doctrine\Tests\Common;

use Doctrine\Common\ClassLoader;

class ClassLoaderTest extends \Doctrine\Tests\DoctrineTestCase
{
    public function testClassLoader()
    {
        $classLoader = new ClassLoader('ClassLoaderTest');
        $classLoader->setIncludePath(__DIR__);
        $classLoader->setFileExtension('.class.php');
        $classLoader->setNamespaceSeparator('_');

        $this->assertTrue($classLoader->canLoadClass('ClassLoaderTest_ClassA'));
        $this->assertTrue($classLoader->canLoadClass('ClassLoaderTest_ClassB'));
        $this->assertTrue($classLoader->canLoadClass('ClassLoaderTest_ClassC'));
        $this->assertFalse($classLoader->canLoadClass('OtherClass'));
        $this->assertEquals($classLoader->loadClass('ClassLoaderTest_ClassA'), true);
        $this->assertEquals($classLoader->loadClass('ClassLoaderTest_ClassB'), true);
        $this->assertEquals($classLoader->loadClass('ClassLoaderTest_ClassC'), true);
    }

    public function testClassExists()
    {
        $this->assertFalse(ClassLoader::classExists('ClassLoaderTest\ClassD'));
        $badLoader = function($className) {
            require __DIR__ . '/ClassLoaderTest/ClassD.php';
            return true;
        };
        spl_autoload_register($badLoader);
        $this->assertTrue(ClassLoader::classExists('ClassLoaderTest\ClassD'));
        spl_autoload_unregister($badLoader);
    }

    public function testGetClassLoader()
    {
        $cl = new ClassLoader('ClassLoaderTest', __DIR__);
        $cl->register();
        $this->assertTrue(ClassLoader::getClassLoader('ClassLoaderTest\ClassD') instanceof \Doctrine\Common\ClassLoader);
        $this->assertNull(ClassLoader::getClassLoader('This\Class\Does\Not\Exist'));
        $cl->unregister();
    }

    public function testClassExistsWithSilentAutoloader()
    {
        $test = $this;
        $silentLoader = function ($className) use ($test) {
            $test->assertSame('ClassLoaderTest\ClassE', $className);
            require __DIR__ . '/ClassLoaderTest/ClassE.php';
        };
        $additionalLoader = function () use ($test) {
            $test->fail('Should not call this loader, class was already loaded');
        };

        $this->assertFalse(ClassLoader::classExists('ClassLoaderTest\ClassE'));
        spl_autoload_register($silentLoader);
        spl_autoload_register($additionalLoader);
        $this->assertTrue(ClassLoader::classExists('ClassLoaderTest\ClassE'));
        spl_autoload_unregister($additionalLoader);
        spl_autoload_unregister($silentLoader);
    }

    public function testClassExistsWhenLoaderIsProtected()
    {
        require_once __DIR__ . '/ClassLoaderTest/ExternalLoader.php';

        // Test static call
        \ClassLoaderTest\ExternalLoader::registerStatic();
        $this->assertFalse(ClassLoader::classExists('ClassLoaderTest\Class\That\Does\Not\Exist'));
        \ClassLoaderTest\ExternalLoader::unregisterStatic();

        // Test object
        $loader = new \ClassLoaderTest\ExternalLoader();
        $loader->register();
        $this->assertFalse(ClassLoader::classExists('ClassLoaderTest\Class\That\Does\Not\Exist'));
        $loader->unregister();
    }

    public function testLoadNonExistingClass()
    {
        $classLoader = new ClassLoader('ClassLoaderTest', __DIR__);

        $this->assertFalse($classLoader->loadClass('ClassLoaderTest\Non\Existing\ClassName'));
    }

    public function testLoadFileNotContainingClassClass()
    {
        $classLoader = new ClassLoader('ClassLoaderTest', __DIR__);

        $classLoader->setFileExtension('.class.php');

        $this->assertFalse($classLoader->loadClass('ClassLoaderTest\EmptyFile'));
    }

    public function testSupportsInterfaceAutoloading()
    {
        $classLoader = new ClassLoader();
        $classLoader->setIncludePath(__DIR__);
        $classLoader->setFileExtension('.class.php');
        $classLoader->setNamespaceSeparator('_');

        $this->assertTrue($classLoader->loadClass('ClassLoaderTest_InterfaceA'));
        $this->assertTrue(interface_exists('ClassLoaderTest_InterfaceA', false));
    }

    public function testSupportsTraitAutoloading()
    {
        if (! function_exists('trait_exists')) {
            $this->markTestSkipped('You need a PHP version that supports traits in order to run this test');
        }

        $classLoader = new ClassLoader();
        $classLoader->setIncludePath(__DIR__);
        $classLoader->setFileExtension('.class.php');
        $classLoader->setNamespaceSeparator('_');

        $this->assertTrue($classLoader->loadClass('ClassLoaderTest_TraitA'));
        $this->assertTrue(trait_exists('ClassLoaderTest_TraitA', false));
    }

    public function testMultipleAutoloadRequestsWillProduceSameResult()
    {
        $classLoader = new ClassLoader();
        $classLoader->setIncludePath(__DIR__);
        $classLoader->setFileExtension('.class.php');
        $classLoader->setNamespaceSeparator('_');

        $this->assertTrue($classLoader->loadClass('ClassLoaderTest_ClassA'));
        $this->assertTrue($classLoader->loadClass('ClassLoaderTest_ClassA'));
    }
}
