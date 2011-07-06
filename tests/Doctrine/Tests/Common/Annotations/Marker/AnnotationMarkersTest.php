<?php

namespace Doctrine\Tests\Common\Annotations\Marker;


use Doctrine\Tests\Common\Annotations\Fixtures\Annotation\AnnnotedAnnotation;

use Doctrine\Common\Annotations\Marker\MarkerStrategy;
use Doctrine\Common\Annotations\Marker\TargetStrategy;
use Doctrine\Common\Annotations\Marker\AnnotationMarkers;
use Doctrine\Common\Annotations\Marker\Annotation\DefaultValue;
use Doctrine\Common\Annotations\Marker\Annotation\Marker;
use Doctrine\Common\Annotations\Marker\Annotation\Target;
use Doctrine\Common\Annotations\Marker\Annotation\Type;

use Doctrine\Common\Annotations\AnnotationReader;
use Doctrine\Common\Annotations\Proxy\ProxyFactory;
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
        $markers    = new AnnotationMarkers($annotation,new AnnotationReader());

        
        $list    = $markers->getAllMarkers();
        $this->assertEquals(5, sizeof($list));
        
        $this->assertTrue($markers->hasClassMarker($target));
        $this->assertFalse($markers->hasClassMarker($this->_marker("Type")));
        
        $list    = $markers->getClassMarkers();
        
        $this->assertEquals(1, sizeof($list));
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
        $markers    = new AnnotationMarkers($annotation,new AnnotationReader());
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
    public function testMethodMarkers()
    {
        $default    = $this->_marker("DefaultValue");
        $annotation = $this->_annot("MarkerdAnnotationInterface");
        $factory    = new \Doctrine\Common\Annotations\Proxy\ProxyFactory(); 
        $proxy      = $factory->proxy(new \ReflectionClass('Doctrine\Tests\Common\Annotations\Fixtures\Annotation\MarkerdAnnotationInterface'));
        
        $markers    = new AnnotationMarkers($proxy,new AnnotationReader());
        $list       = $markers->getMethodsMarkers();
        
        
        $this->assertEquals(3, sizeof($list));
        
        $this->assertTrue(array_key_exists("name", $list));
        $this->assertTrue(array_key_exists("target", $list));
        
        $this->assertTrue($list['name'][0] instanceof DefaultValue);
        $this->assertTrue($list['name'][0] instanceof Marker);
        
        $this->assertTrue($list['target'][0] instanceof DefaultValue);
        $this->assertTrue($list['target'][0] instanceof Marker);
        
        $this->assertTrue($list['target'][1] instanceof Type);
        $this->assertTrue($list['target'][1] instanceof Marker);
        
        
        
        $this->assertTrue($markers->hasMethodMarker($default,"name"));
        $this->assertFalse($markers->hasMethodMarker($this->_marker("Target"),"name"));
        
        $this->assertTrue($markers->hasMethodMarker($default,"target"));
        $this->assertFalse($markers->hasMethodMarker($this->_marker("Target"),"target"));
        
        
        $list = $markers->getMethodMarkers("name");
        $this->assertEquals(1, sizeof($list));
        $this->assertTrue($list[0] instanceof DefaultValue);
        
        
        $list = $markers->getMethodMarkers("target");
        $this->assertEquals(2, sizeof($list));
        $this->assertTrue($list[0] instanceof DefaultValue);
        $this->assertTrue($list[1] instanceof Type);
        
        
        $defaultValue = $markers->getMethodMarker($default,'name');
        $this->assertTrue($defaultValue instanceof DefaultValue);
        $this->assertEquals($defaultValue->value, "Foo Value");
        
        $defaultValue = $markers->getMethodMarker($default,'target');
        $this->assertTrue($defaultValue instanceof DefaultValue);
        $this->assertTrue($defaultValue->value instanceof Target);
        $this->assertEquals($defaultValue->value->value, Target::TARGET_ALL);
    }
    
}