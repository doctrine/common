<?php

namespace Doctrine\Tests\Common\Annotations\Proxy;

use Doctrine\Common\Annotations\AnnotationFactory;
use Doctrine\Tests\Common\Annotations\Fixtures\Annotation\MyAnnotation;
use Doctrine\Tests\Common\Annotations\Fixtures\Annotation\MyAnnotationImpl;


class AnnotationFactoryTest extends \PHPUnit_Framework_TestCase
{
    
    /**
     * @param   string $name
     * @return  string
     */
    private function _class($name)
    {
        return new \ReflectionClass("Doctrine\\Tests\\Common\\Annotations\\Fixtures\\Annotation\\".$name);
    }

    
    /**
     * @group proxy
     * @group factory
     */
    public function testNewAnnotationProxy()
    {
        $factory    = new AnnotationFactory($this->_class("MyAnnotation"));
        $annot      = $factory->newAnnotation(array("data"=>"Some data"));
        
        $this->assertNotNull($annot);
        $this->assertTrue($annot instanceof MyAnnotation);
        
        $this->assertNull($annot->name());
        $this->assertNotNull($annot->data());
        $this->assertEquals($annot->data(), "Some data");
        $this->assertEquals($annot->data(), $annot->data);
    }
    
     
    /**
     * @group proxy
     * @group factory
     */
    public function testNewAnnotation()
    {
        $class      = $this->_class("MyAnnotationImpl");
        $factory    = new AnnotationFactory($class);
        $annot      = $factory->newAnnotation(array("data"=>"Some data"));
        
        $this->assertNotNull($annot);
        $this->assertTrue($annot instanceof MyAnnotation);
        $this->assertTrue($annot instanceof MyAnnotationImpl);
        $this->assertNull($annot->name());
        $this->assertNotNull($annot->data());
        $this->assertEquals($annot->data(), "Some data");
        $this->assertEquals($annot->data(), $annot->data);
    }
    
    
    /**
     * @group proxy
     * @group factory
     */
    public function testClassNameProxy()
    {
        $class      = $this->_class("MyAnnotationImpl");
        $factory    = new AnnotationFactory($class);
        $this->assertEquals($class->getName(), $factory->getClassName());
        
        
        $class      = new \ReflectionClass($this->_class("MyAnnotation"));
        $factory    = new AnnotationFactory($class);
        $this->assertEquals($class->getName(), $factory->getClassName());
    }
    
    
}


class SomeAnnotationClassName implements \Doctrine\Common\Annotations\Annotation\Annotation
{

}