<?php

namespace Doctrine\Tests\Common;

use ClassLoaderTest\ExternalLoader;
use Doctrine\Common\ClassLoader;
use Doctrine\Tests\DoctrineTestCase;

use function interface_exists;
use function spl_autoload_register;
use function spl_autoload_unregister;
use function trait_exists;

/**
 * @group legacy
 */
class ClassLoaderTest extends DoctrineTestCase
{
    public function testClassLoader()
    {
        $classLoader = new ClassLoader('ClassLoaderTest');
        $classLoader->setIncludePath(__DIR__);
        $classLoader->setFileExtension('.class.php');
        $classLoader->setNamespaceSeparator('_');

        self::assertTrue($classLoader->canLoadClass('ClassLoaderTest_ClassA'));
        self::assertTrue($classLoader->canLoadClass('ClassLoaderTest_ClassB'));
        self::assertTrue($classLoader->canLoadClass('ClassLoaderTest_ClassC'));
        self::assertFalse($classLoader->canLoadClass('OtherClass'));
        self::assertEquals($classLoader->loadClass('ClassLoaderTest_ClassA'), true);
        self::assertEquals($classLoader->loadClass('ClassLoaderTest_ClassB'), true);
        self::assertEquals($classLoader->loadClass('ClassLoaderTest_ClassC'), true);
    }

    public function testClassExists()
    {
        self::assertFalse(ClassLoader::classExists('ClassLoaderTest\ClassD'));
        $badLoader = static function ($className) {
            require __DIR__ . '/ClassLoaderTest/ClassD.php';

            return true;
        };
        spl_autoload_register($badLoader);
        self::assertTrue(ClassLoader::classExists('ClassLoaderTest\ClassD'));
        spl_autoload_unregister($badLoader);
    }

    public function testGetClassLoader()
    {
        $cl = new ClassLoader('ClassLoaderTest', __DIR__);
        $cl->register();
        self::assertTrue(ClassLoader::getClassLoader('ClassLoaderTest\ClassD') instanceof ClassLoader);
        self::assertNull(ClassLoader::getClassLoader('This\Class\Does\Not\Exist'));
        $cl->unregister();
    }

    public function testClassExistsWithSilentAutoloader()
    {
        $test             = $this;
        $silentLoader     = static function ($className) use ($test) {
            $test->assertSame('ClassLoaderTest\ClassE', $className);
            require __DIR__ . '/ClassLoaderTest/ClassE.php';
        };
        $additionalLoader = static function () use ($test) {
            $test->fail('Should not call this loader, class was already loaded');
        };

        self::assertFalse(ClassLoader::classExists('ClassLoaderTest\ClassE'));
        spl_autoload_register($silentLoader);
        spl_autoload_register($additionalLoader);
        self::assertTrue(ClassLoader::classExists('ClassLoaderTest\ClassE'));
        spl_autoload_unregister($additionalLoader);
        spl_autoload_unregister($silentLoader);
    }

    public function testClassExistsWhenLoaderIsProtected()
    {
        require_once __DIR__ . '/ClassLoaderTest/ExternalLoader.php';

        // Test static call
        ExternalLoader::registerStatic();
        self::assertFalse(ClassLoader::classExists('ClassLoaderTest\Class\That\Does\Not\Exist'));
        ExternalLoader::unregisterStatic();

        // Test object
        $loader = new ExternalLoader();
        $loader->register();
        self::assertFalse(ClassLoader::classExists('ClassLoaderTest\Class\That\Does\Not\Exist'));
        $loader->unregister();
    }

    public function testLoadNonExistingClass()
    {
        $classLoader = new ClassLoader('ClassLoaderTest', __DIR__);

        self::assertFalse($classLoader->loadClass('ClassLoaderTest\Non\Existing\ClassName'));
    }

    public function testLoadFileNotContainingClassClass()
    {
        $classLoader = new ClassLoader('ClassLoaderTest', __DIR__);

        $classLoader->setFileExtension('.class.php');

        self::assertFalse($classLoader->loadClass('ClassLoaderTest\EmptyFile'));
    }

    public function testSupportsInterfaceAutoloading()
    {
        $classLoader = new ClassLoader();
        $classLoader->setIncludePath(__DIR__);
        $classLoader->setFileExtension('.class.php');
        $classLoader->setNamespaceSeparator('_');

        self::assertTrue($classLoader->loadClass('ClassLoaderTest_InterfaceA'));
        self::assertTrue(interface_exists('ClassLoaderTest_InterfaceA', false));
    }

    public function testSupportsTraitAutoloading()
    {
        $classLoader = new ClassLoader();
        $classLoader->setIncludePath(__DIR__);
        $classLoader->setFileExtension('.class.php');
        $classLoader->setNamespaceSeparator('_');

        self::assertTrue($classLoader->loadClass('ClassLoaderTest_TraitA'));
        self::assertTrue(trait_exists('ClassLoaderTest_TraitA', false));
    }

    public function testMultipleAutoloadRequestsWillProduceSameResult()
    {
        $classLoader = new ClassLoader();
        $classLoader->setIncludePath(__DIR__);
        $classLoader->setFileExtension('.class.php');
        $classLoader->setNamespaceSeparator('_');

        self::assertTrue($classLoader->loadClass('ClassLoaderTest_ClassA'));
        self::assertTrue($classLoader->loadClass('ClassLoaderTest_ClassA'));
    }
}
