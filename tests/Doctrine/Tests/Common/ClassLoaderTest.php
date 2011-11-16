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
        
        // class loader with return true statement on success
        $badLoader = function($className) {
            require __DIR__ . '/ClassLoaderTest/ClassD.php';
            return true;
        };
        spl_autoload_register($badLoader);
        $this->assertTrue(ClassLoader::classExists('ClassLoaderTest\ClassD'));
        spl_autoload_unregister($badLoader);
        
        $this->assertFalse(ClassLoader::classExists('ClassLoaderTest\ClassE'));
        
        // test class loader without return true statement on success like symfony (and probably many others)
        $badLoader = function($className) {
            require_once __DIR__ . '/ClassLoaderTest/ClassE.php';
            // no return true
        };
        spl_autoload_register($badLoader);
        $this->assertTrue(ClassLoader::classExists('ClassLoaderTest\ClassE')); // Fails w/o fix
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
    
    
    public function testClassExistsMultipleLoaders()
    {
        // sometimes you may want to split source directories (eg, src, test-src)
        // but the namespaces are the same for bot...
        $classLoader1 = new ClassLoader('NS', __DIR__.'/ClassLoaderTest2/src1');
        $classLoader1->register();
    
        $classLoader2 = new ClassLoader('NS', __DIR__.'/ClassLoaderTest2/src2');
        $classLoader2->register();
    
        $this->assertTrue(ClassLoader::classExists('NS\\Class1'));
        new \NS\Class1();
        $this->assertTrue(ClassLoader::classExists('NS\\Class2'));
        new \NS\Class2(); // fatal error (file not found) even if loadable via classLoader2
        
        $classLoader1->unregister();
        $classLoader2->unregister();
    }
}
