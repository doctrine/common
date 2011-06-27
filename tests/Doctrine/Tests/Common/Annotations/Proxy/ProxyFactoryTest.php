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
     * @expectedException InvalidArgumentException
     */
    public function testRegistertException()
    {
        $factory = new ProxyFactory();
        $factory->register(
                $this->fullClassName("MyAnnotation"),
                $this->fullClassName("MyAnnotationImplInvalid"));
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