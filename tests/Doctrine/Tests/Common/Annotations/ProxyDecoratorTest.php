<?php

namespace Doctrine\Tests\Common\Annotations\Proxy;

use Doctrine\Common\Annotations\Proxy\ProxyDecorator;
use Doctrine\Common\Annotations\Proxy\AbstractProxy;

use Doctrine\Tests\Common\Annotations\Fixtures\Annotation\MyAnnotation;
use Doctrine\Tests\Common\Annotations\Fixtures\Annotation\MyAnnotationImpl;


class ProxyDecoratorTest extends \PHPUnit_Framework_TestCase
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
     * @param   string $name
     * @return  string
     */
    private function getClass($name)
    {
        return new \ReflectionClass($this->fullClassName($name));
    }


    /**
     * @group proxy
     * @group proxy-decorator
     */
    public function testSetSingleValue()
    {
        $decorator  = new ProxyDecorator($this->getClass("MyAnnotationImpl"));
        $proxy      = new MyAnnotationImpl(array());
        
        
        $condition  = $decorator->setData($proxy, array("data"=>"Some data"));
        $this->assertEquals($proxy->data(), "Some data");
        $this->assertEquals($proxy->data(), $proxy->data);
        
        $condition  = $decorator->setData($proxy, array("name"=>"Some name"));
        $this->assertEquals($proxy->name(), "Some name");
        $this->assertEquals($proxy->name(), $proxy->name);
        
        $condition  = $decorator->setData($proxy, array("value"=>"Some name"));
        $this->assertEquals($proxy->name(), "Some name");
        $this->assertEquals($proxy->name(), $proxy->name);

        
        $decorator  = new ProxyDecorator($this->getClass("MyAnnotationImpl"));
        $proxy      = new MyAnnotationImpl(array());
        $condition  = $decorator->setData($proxy, array("value"=>"Some name"));
        $this->assertEquals($proxy->name(), "Some name");
        
        $condition  = $decorator->setData($proxy, array("name"=>"Some name"));
        $this->assertEquals($proxy->name(), "Some name");
    }
    
    
    /**
     * @group proxy
     * @group proxy-decorator
     */
    public function testSetValues()
    {
        $decorator  = new ProxyDecorator($this->getClass("MyAnnotationImpl"));
        $proxy      = new MyAnnotationImpl(array());
        
        $condition  = $decorator->setData($proxy, array("name"=>"Some name",'data'=>"Some data"));
        $this->assertEquals($proxy->name(), "Some name");
        $this->assertEquals($proxy->data(), "Some data");
        
        $condition  = $decorator->setData($proxy, array("value"=>"Some name",'data'=>"Some data"));
        $this->assertEquals($proxy->name(), "Some name");
        $this->assertEquals($proxy->data(), "Some data");
    }
    
    /**
     * @group proxy
     * @group proxy-decorator
     * @expectedException \BadMethodCallException
     */
    public function testSetValuesExeption()
    {
        $decorator  = new ProxyDecorator($this->getClass("MyAnnotationImpl"));
        $proxy      = new MyAnnotationImpl(array());
        
        $condition  = $decorator->setData($proxy, array("invalidaProperty"=>"Some value"));
    }

}
