<?php

namespace Doctrine\Tests\Common\Annotations\Proxy;

use Doctrine\Common\Annotations\Proxy\ProxyFactory;
use Doctrine\Common\Annotations\Proxy\AbstractProxy;
use Doctrine\Tests\Common\Annotations\Fixtures\Annotation\MyAnnotation;
use Doctrine\Tests\Common\Annotations\Fixtures\Annotation\MyAnnotationImpl;


class ProxyFactoryTest extends \PHPUnit_Framework_TestCase
{
    
    /**
     * @param   string $name
     * @return  \ReflectionClass
     */
    private function _class($name)
    {
        return new \ReflectionClass("Doctrine\\Tests\\Common\\Annotations\\Fixtures\\Annotation\\".$name);
    }

    
    /**
     * @group proxy
     * @group proxy-factory
     */
    public function testRegister()
    {
        $factory = new ProxyFactory();
        $factory->register(
                $this->_class("MyAnnotation"),
                $this->_class("MyAnnotationImpl"));
        
        $this->assertTrue(true);
    }
    
    
     /**
     * @group proxy
     * @group proxy-factory
     */
    public function testUnRegister()
    {
        $factory = new ProxyFactory();
        $factory->register(
                $this->_class("MyAnnotation"),
                $this->_class("MyAnnotationImpl"));
        
        $factory->unregister($this->_class("MyAnnotation"));
        $this->assertTrue(true);
    }
    
    
    /**
     * @group proxy
     * @group proxy-factory
     */
    public function testHasProxy()
    {
        $factory = new ProxyFactory();
        $factory->register(
                $this->_class("MyAnnotation"),
                $this->_class("MyAnnotationImpl"));
        
        $this->assertTrue($factory->hasProxy($this->_class("MyAnnotation")));
    }
    
    /**
     * @group proxy
     * @group proxy-factory
     */
    public function testIsProxy()
    {
        $factory = new ProxyFactory();
        $factory->register(
                $this->_class("MyAnnotation"),
                $this->_class("MyAnnotationImpl"));
        
        $this->assertTrue($factory->isProxy($this->_class("MyAnnotationImpl")));
    }
    
       
    
    /**
     * @group proxy
     * @group proxy-factory
     */
    public function testGetClass()
    {
        $factory = new ProxyFactory();
        $factory->register(
                $this->_class("MyAnnotation"),
                $this->_class("MyAnnotationImpl"));
        
        $class = $factory->getClass($this->_class("MyAnnotationImpl"));
        
        $this->assertNotNull($class);
        $this->assertEquals($this->_class("MyAnnotation")->getName(), $class->getName());
    }
    
    /**
     * @group proxy
     * @group proxy-factory
     */
    public function testGetProxy()
    {
        $factory = new ProxyFactory();
        $factory->register(
                $this->_class("MyAnnotation"),
                $this->_class("MyAnnotationImpl"));
        
        $proxy = $factory->getProxy($this->_class("MyAnnotation"));
        
        $this->assertNotNull($proxy);
        $this->assertEquals($this->_class("MyAnnotationImpl")->getName(), $proxy->getName());
    }
    
    
    /**
     * @group proxy
     * @group proxy-factory
     */
    public function testProxy()
    {
        $factory    = new ProxyFactory();
        $interface  = $this->_class("MyAnnotation");
        $proxy      = $factory->proxy($interface);

        $this->assertNotNull($proxy);
        $this->assertTrue($proxy->implementsInterface($interface));
    }
    
    
    /**
     * @expectedException \InvalidArgumentException
     * @expectedExceptionMessage Class "Doctrine\Tests\Common\Annotations\Fixtures\Annotation\MyAnnotation" already has a proxy
     */
    public function testExceptionAlreadyHasProxy()
    {
        $factory = new ProxyFactory();
        $factory->register(
                $this->_class("MyAnnotation"),
                $this->_class("MyAnnotationImpl"));
        
        $factory->register(
                $this->_class("MyAnnotation"),
                new \ReflectionClass(__NAMESPACE__."\\ClassExtendsImpl"));
    }
    
    
    /**
     * @expectedException \InvalidArgumentException
     * @expectedExceptionMessage Class "Doctrine\Tests\Common\Annotations\Fixtures\Annotation\MyAnnotation" is not registered
     */
    public function testExceptionUnregister()
    {
        $factory = new ProxyFactory();
        $factory->unregister($this->_class("MyAnnotation"));
    }
    
    /**
     * @expectedException \InvalidArgumentException
     * @expectedExceptionMessage Class "Doctrine\Tests\Common\Annotations\Proxy\ClassNotImplementsInterface" not implements "Doctrine\Tests\Common\Annotations\Fixtures\Annotation\MyAnnotation"
     */
    public function testExceptionImplNotImplements()
    {
        $factory = new ProxyFactory();
        $factory->register(
                $this->_class("MyAnnotation"),
                new \ReflectionClass(__NAMESPACE__.'\\'.'ClassNotImplementsInterface'));
    }
    
    
    /**
     * @expectedException \RuntimeException
     * @expectedExceptionMessage "Doctrine\Tests\Common\Annotations\Proxy\MyAnnotationWithParams" can not have parameters at function "name"
     */
    public function testFunctionWithParamsExeption()
    {
        $factory    = new ProxyFactory();
        $class      = new \ReflectionClass("Doctrine\\Tests\\Common\\Annotations\\Proxy\\MyAnnotationWithParams");
        $factory->proxy($class);
    }
 
    
}


interface MyAnnotationWithParams
{
    function name($args1);
    function data();
}

class ClassNotImplementsInterface
{

}
class ClassExtendsImpl extends MyAnnotationImpl
{
    
}