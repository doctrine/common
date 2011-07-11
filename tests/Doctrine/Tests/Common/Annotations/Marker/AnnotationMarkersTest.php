<?php

namespace Doctrine\Tests\Common\Annotations\Marker;

use Doctrine\Common\Annotations\Marker\AnnotationMarkers;
use Doctrine\Common\Annotations\Marker\Annotation\DefaultValue;
use Doctrine\Common\Annotations\Marker\Annotation\Marker;
use Doctrine\Common\Annotations\Marker\Annotation\Target;
use Doctrine\Common\Annotations\Marker\Annotation\Type;

use Doctrine\Common\Annotations\AnnotationFactory;
use Doctrine\Common\Annotations\AnnotationException;


class AnnotationMarkersTest extends \PHPUnit_Framework_TestCase
{
    
    /**
     * @param   string $name
     * @return  \ReflectionClass
     */
    private function _annot($name)
    {
        return new \ReflectionClass("Doctrine\\Tests\\Common\\Annotations\\Fixtures\\Annotation\\".$name);
    }
    
    /**
     * @param   string $name
     * @return  string
     */
    private function _marker($name)
    {
        return "Doctrine\\Common\\Annotations\\Marker\\Annotation\\".$name;
    }

    
    /**
     * @group Marker
     */
    public function testClassMarkers()
    {
        $annotation = $this->_annot("MarkedAnnotation");
        $target     = $this->_marker("Target");
        $markers    = new AnnotationMarkers($annotation);

        
        $list    = $markers->getAllMarkers();
        $this->assertEquals(6, sizeof($list));
        
        $this->assertTrue($markers->hasClassMarker($target));
        $this->assertFalse($markers->hasClassMarker($this->_marker("Type")));
        
        $list    = $markers->getClassMarkers();
        
        $this->assertEquals(2, sizeof($list));
        $this->assertTrue($list[0] instanceof Marker);
        $this->assertTrue($list[0] instanceof Target);
        
        $target = $markers->getClassMarker($this->_marker("Target"));        
        $this->assertTrue($target instanceof Marker);
        $this->assertTrue($target instanceof Target);
        $this->assertEquals($target->value, Target::TARGET_ALL);
    }
    
    
    /**
     * @group Marker
     */
    public function testPropertyMarkers()
    {
        $default    = $this->_marker("DefaultValue");
        $annotation = $this->_annot("MarkedAnnotation");
        $markers    = new AnnotationMarkers($annotation);
        $list       = $markers->getPropertiesMarkers();
        
        
        $this->assertEquals(3, sizeof($list));
        
        $this->assertTrue(array_key_exists("name", $list));
        $this->assertTrue(array_key_exists("target", $list));
        
        $this->assertTrue($list['name'][0] instanceof DefaultValue);
        $this->assertTrue($list['name'][0] instanceof Marker);
        
        $this->assertTrue($list['target'][0] instanceof DefaultValue);
        $this->assertTrue($list['target'][0] instanceof Marker);
        
        $this->assertTrue($list['target'][1] instanceof Type);
        $this->assertTrue($list['target'][1] instanceof Marker);
        
        
        
        $this->assertTrue($markers->hasPropertyMarker($default,"name"));
        $this->assertFalse($markers->hasPropertyMarker($this->_marker("Target"),"name"));
        
        $this->assertTrue($markers->hasPropertyMarker($default,"target"));
        $this->assertFalse($markers->hasPropertyMarker($this->_marker("Target"),"target"));
        
        
        $list = $markers->getPropertyMarkers("name");
        $this->assertEquals(1, sizeof($list));
        $this->assertTrue($list[0] instanceof DefaultValue);
        
        
        $list = $markers->getPropertyMarkers("target");
        $this->assertEquals(2, sizeof($list));
        $this->assertTrue($list[0] instanceof DefaultValue);
        $this->assertTrue($list[1] instanceof Type);
        
        
        $defaultValue = $markers->getPropertyMarker($default,'name');
        $this->assertTrue($defaultValue instanceof DefaultValue);
        $this->assertEquals($defaultValue->value, "Foo Value");
        
        $defaultValue = $markers->getPropertyMarker($default,'target');
        $this->assertTrue($defaultValue instanceof DefaultValue);
        $this->assertTrue($defaultValue->value instanceof Target);
        $this->assertEquals($defaultValue->value->value, Target::TARGET_ALL);
    }

    
     /**
     * @group Marker
     */
    public function testRunMarker()
    {
        $class      = new \ReflectionClass('Doctrine\Tests\Common\Annotations\Fixtures\Annotation\MarkedAnnotation');
        $markers    = new AnnotationMarkers($class);
        $marker     = $markers->getPropertyMarker('Doctrine\Common\Annotations\Marker\Annotation\DefaultValue', 'name');
        $target     = new \ReflectionClass('Doctrine\Tests\Common\Annotations\Fixtures\MarkedClassName');
        $annotation = $class->newInstance();
        
        
        
        $this->assertNull($annotation->name);

        $markers->runMarker($annotation, $target, $marker);

        $this->assertEquals($annotation->name, "Foo Value");
    }
    
    
    
    
     
     /**
     * @group Marker
     */
    public function testIsMarkerd()
    {
        $class = new \ReflectionClass(__NAMESPACE__ . '\MarkedClassAnnotation');
        $this->assertTrue(AnnotationMarkers::isMarked($class));
        
        $class = new \ReflectionClass(__NAMESPACE__ . '\NotMarkedClassAnnotation');
        $this->assertFalse(AnnotationMarkers::isMarked($class));
    }
    
    
      /**
     * @group Marker
     * @expectedException \InvalidArgumentException
     * @expectedExceptionMessage Argument 1 must be an instance of "Doctrine\Tests\Common\Annotations\Fixtures\Annotation\AnnotationTargetClass", "stdClass" given.
     */
    public function testRunMarkerExceptionTarget()
    {
        $class      = new \ReflectionClass('Doctrine\Tests\Common\Annotations\Fixtures\Annotation\AnnotationTargetClass');
        $markers    = new AnnotationMarkers($class);
        $target     = new \ReflectionClass('Doctrine\Tests\Common\Annotations\Fixtures\MarkedClassName');
        $annotation = $class->newInstance();
        
        $markers->runMarkers(new \stdClass, $target);
    }
    
    
     /**
     * @group Marker
     * @expectedException \InvalidArgumentException
     * @expectedExceptionMessage Annotation 'Doctrine\Tests\Common\Annotations\Marker\SubclassOfMarker' can not be a sub class of Marker.
     */
    public function testisSubclassOfMarker()
    {
        $annotation = new \ReflectionClass(__NAMESPACE__ . '\SubclassOfMarker');
        $markers    = new AnnotationMarkers($annotation);
    }
    
     
     /**
     * @group Marker
     * @expectedException \InvalidArgumentException
     * @expectedExceptionMessage  Class 'Doctrine\Tests\Common\Annotations\Marker\NotMarkedClassAnnotation' is not marked with @Marked.
     */
    public function testisSubclassOfMarkerException()
    {
        $annotation = new \ReflectionClass(__NAMESPACE__ . '\NotMarkedClassAnnotation');
        $markers    = new AnnotationMarkers($annotation);
    }
    
    
}

/**
 * @Target("CLASS")
 * @Annotation
 * @Marked
 */
class MarkedClassAnnotation
{
    
}

/**
 * @Annotation
 */
class NotMarkedClassAnnotation 
{
    
}

/**
 * @Annotation
 */
class SubclassOfMarker extends Marker 
{
    public function strategyClass()
    {
        return null;
    }
    
    public function priority()
    {
        return 0;
    }  
}
