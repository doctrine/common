<?php

namespace Doctrine\Tests\Common\Annotations\Proxy;

use Doctrine\Common\Annotations\AnnotationFactory;
use Doctrine\Tests\Common\Annotations\Fixtures\Annotation\MyAnnotation;


class AnnotationFactoryTest extends \PHPUnit_Framework_TestCase
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
     * @group factory
     */
    public function testNewAnnotation()
    {
        $class      = new \ReflectionClass($this->fullClassName("MyAnnotationImpl"));
        $factory    = new AnnotationFactory($class);
        $annot      = $factory->newAnnotation(array("data"=>"Some data"));
        
        $this->assertNotNull($annot);
        $this->assertInstanceOf($this->fullClassName("MyAnnotation"),$annot);
        $this->assertInstanceOf($this->fullClassName("MyAnnotationImpl"),$annot);
        
        $this->assertNull($annot->name());
        $this->assertNotNull($annot->data());
        $this->assertEquals($annot->data(), "Some data");
        
        
        $class      = new \ReflectionClass($this->fullClassName("MyAnnotation"));
        $factory    = new AnnotationFactory($class);
        $annot      = $factory->newAnnotation(array("data"=>"Some data"));
        
        $this->assertNotNull($annot);
        $this->assertInstanceOf($this->fullClassName("MyAnnotation"),$annot);
        
        $this->assertNull($annot->name());
        $this->assertNotNull($annot->data());
        $this->assertEquals($annot->data(), "Some data");
        $this->assertEquals($annot->data(), $annot->data);
    }
    
    
    /**
     * @group proxy
     * @group factory
     */
    public function testClassName()
    {
        $class      = new \ReflectionClass($this->fullClassName("MyAnnotationImpl"));
        $factory    = new AnnotationFactory($class);
        $this->assertEquals($class->getName(), $factory->getClassName());
        
        
        $class      = new \ReflectionClass($this->fullClassName("MyAnnotation"));
        $factory    = new AnnotationFactory($class);
        $this->assertEquals($class->getName(), $factory->getClassName());
    }
    
}
