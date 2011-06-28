<?php

namespace Doctrine\Tests\Common\Annotations\Proxy;

use Doctrine\Common\Annotations\Proxy\ProxyFactory;
use Doctrine\Common\Annotations\Proxy\AbstractProxy;
use Doctrine\Tests\Common\Annotations\Fixtures\Annotation\MyAnnotation;


class ProxyFactoryTest extends \PHPUnit_Framework_TestCase
{
    
    /**
     * @param   string $name
     * @return  string
     */
    private function fullClassName($name)
    {
        return "Doctrine\\Tests\\Common\\Annotations\\Fixtures\\Annotation\\".$name;
    }

    
    /**
     * @group proxy
     * @group proxy-factory
     */
    public function testRegister()
    {
        $factory = new ProxyFactory();
        $factory->register(
                $this->fullClassName("MyAnnotation"),
                $this->fullClassName("MyAnnotationImpl"));
        
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
                $this->fullClassName("MyAnnotation"),
                $this->fullClassName("MyAnnotationImpl"));
        
        $factory->unregister($this->fullClassName("MyAnnotation"));
        $this->assertTrue(true);
    }
    
    
    /**
     * @group proxy
     * @group proxy-factory
     */
    public function testIsRegistered()
    {
        $factory = new ProxyFactory();
        $factory->register(
                $this->fullClassName("MyAnnotation"),
                $this->fullClassName("MyAnnotationImpl"));
        
        $condition = $factory->isRegistered($this->fullClassName("MyAnnotation"));
        $this->assertTrue($condition);
    }
    
       
    
    /**
     * @group proxy
     * @group proxy-factory
     */
    public function testGetInterface()
    {
        $factory = new ProxyFactory();
        $factory->register(
                $this->fullClassName("MyAnnotation"),
                $this->fullClassName("MyAnnotationImpl"));
        
        $interface = $factory->getInterface($this->fullClassName("MyAnnotationImpl"));
        
        $this->assertEquals($this->fullClassName("MyAnnotation"), $interface);
    }
    
    
    
    /**
     * @group proxy
     * @group proxy-factory
     */
    public function testGetImplClass()
    {
        $factory = new ProxyFactory();
        $factory->register(
                $this->fullClassName("MyAnnotation"),
                $this->fullClassName("MyAnnotationImpl"));
        
        $impl = $factory->getImplClass($this->fullClassName("MyAnnotation"));
        $this->assertEquals($this->fullClassName("MyAnnotationImpl"), $impl);
        
        
        $args       = array();
        $factory    = new ProxyFactory();
        $interface  = $this->fullClassName("MyAnnotation");
        $class      = $factory->getImplClass($interface);
        
        $this->assertTrue(in_array($interface, class_implements($class)));
        $this->assertTrue(in_array(ProxyFactory::PROXY_BASE, class_parents($class)));
    }
    
    
     /**
     * @group proxy
     * @group proxy-factory
     */
    public function testProxyValues()
    {
        $factory    = new ProxyFactory();
        $interface  = $this->fullClassName("MyAnnotation");
        $args       = array("name"=>"Some name",'data'=>"Some data");
        $class      = $factory->getImplClass($interface);
        
        $this->assertTrue(in_array($interface, class_implements($class)));
        $proxy = new $class($args);
        
        
        $this->assertEquals($proxy->name(), "Some name");
        $this->assertEquals($proxy->data(), "Some data");
        
        $this->assertEquals($proxy->name(), $proxy->name);
        $this->assertEquals($proxy->data(), $proxy->data);
    }
    
    
    
    
    /**
     * @expectedException \InvalidArgumentException
     * @expectedExceptionMessage Interface "Doctrine\Tests\Common\Annotations\Fixtures\Annotation\MyAnnotation" is already registered
     */
    public function testExceptionInterfaceRegistered()
    {
        $factory = new ProxyFactory();
        $factory->register(
                $this->fullClassName("MyAnnotation"),
                $this->fullClassName("MyAnnotationImpl"));
        
        $factory->register(
                $this->fullClassName("MyAnnotation"),
                $this->fullClassName("MyAnnotationImplInvalid"));
    }
    
    /**
     * @expectedException \InvalidArgumentException
     * @expectedExceptionMessage Interface "Doctrine\Tests\Common\Annotations\Fixtures\Annotation\InvalidAnnotationInterface" not found
     */
    public function testExceptionInterfaceNotFound()
    {
        $factory = new ProxyFactory();
        $factory->register(
                $this->fullClassName("InvalidAnnotationInterface"),
                $this->fullClassName("MyAnnotationImpl"));
    }
    
     /**
     * @expectedException \InvalidArgumentException
     * @expectedExceptionMessage class "Doctrine\Tests\Common\Annotations\Fixtures\Annotation\InvalidAnnotationImpl" not found
     */
    public function testExceptionImplNotFound()
    {
        $factory = new ProxyFactory();
        $factory->register(
                $this->fullClassName("MyAnnotation"),
                $this->fullClassName("InvalidAnnotationImpl"));
    }
    
    
    /**
     * @expectedException \InvalidArgumentException
     * @expectedExceptionMessage Interface "Doctrine\Tests\Common\Annotations\Fixtures\Annotation\MyAnnotation" is not registered
     */
    public function testExceptionUnregister()
    {
        $factory = new ProxyFactory();
        $factory->unregister($this->fullClassName("MyAnnotation"));
    }
    
    /**
     * @expectedException \InvalidArgumentException
     * @expectedExceptionMessage Doctrine\Tests\Common\Annotations\Proxy\ClassNotImplementsInterface" not implements "Doctrine\Tests\Common\Annotations\Fixtures\Annotation\MyAnnotation"
     */
    public function testExceptionImplNotImplements()
    {
        $factory = new ProxyFactory();
        $factory->register(
                $this->fullClassName("MyAnnotation"),
                __NAMESPACE__.'\\'.'ClassNotImplementsInterface');
    }
    
    
    /**
     * @expectedException \InvalidArgumentException
     * @expectedExceptionMessage class "Doctrine\Tests\Common\Annotations\Proxy\ClassNotExtendsProxy" not extends "Doctrine\Common\Annotations\Proxy\AbstractProxy"
     */
    public function testExceptionImplNotExtendsProxy()
    {
        $factory = new ProxyFactory();
        $factory->register(
                $this->fullClassName("MyAnnotation"),
                __NAMESPACE__.'\\'.'ClassNotExtendsProxy');
    }
    
    
    /**
     * @expectedException \RuntimeException
     * @expectedExceptionMessage Interface "Doctrine\Tests\Common\Annotations\Proxy\MyAnnotationWithParams" can not have parameters at function "name"
     */
    public function testFunctionWithParamsExeption()
    {
        $factory    = new ProxyFactory();
        $interface  = "Doctrine\\Tests\\Common\\Annotations\\Proxy\\MyAnnotationWithParams";
        $factory->getImplClass($interface);
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
class ClassNotExtendsProxy implements MyAnnotation
{
    function name(){}
    function data(){}
}