<?php

namespace Doctrine\Tests\Common\Annotations\Proxy;


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
     * @return  \ReflectionClass
     */
    private function _marker($name)
    {
        return new \ReflectionClass("Doctrine\\Common\\Annotations\\Marker\\Annotation\\".$name);
    }

    
    /**
     * @group Marker
     */
    public function testClassMarkers()
    {
        $class      = $this->_annot("AnnnotedAnnotation");
        $target     = $this->_marker("Target");
        
        $annotMarkers   = new AnnotationMarkers($class,new AnnotationReader());

        
        $list    = $annotMarkers->getMarkers();
        $this->assertEquals(4, sizeof($list));
        
        
        $this->assertTrue($annotMarkers->hasClassMarker($target));
        $this->assertFalse($annotMarkers->hasClassMarker($this->_marker("Type")));
        
        $list    = $annotMarkers->getClassMarkers();
        
        $this->assertEquals(1, sizeof($list));
        $this->assertTrue($list[0] instanceof Marker);
        $this->assertTrue($list[0] instanceof Target);
        
        $target = $annotMarkers->getClassMarker($this->_marker("Target"));        
        $this->assertTrue($target instanceof Marker);
        $this->assertTrue($target instanceof Target);
        $this->assertEquals($target->value, Target::TARGET_ALL);
    }
    
     
    
    /**
     * @group Marker
     */
    public function testMethodMarkers()
    {
        $default    = $this->_marker("DefaultValue");
        $class      = $this->_annot("AnnnotedAnnotation");
        $metadata   = new AnnotationMarkers($class,new AnnotationReader());
        
        $markers    = $metadata->getMethodsMarkers();
        
        $this->assertEquals(2, sizeof($markers));
        
        $this->assertTrue(array_key_exists("name", $markers));
        $this->assertTrue(array_key_exists("target", $markers));
        
        $this->assertTrue($markers['name'][0] instanceof DefaultValue);
        $this->assertTrue($markers['name'][0] instanceof Marker);
        
        $this->assertTrue($markers['target'][0] instanceof DefaultValue);
        $this->assertTrue($markers['target'][0] instanceof Marker);
        
        $this->assertTrue($markers['target'][1] instanceof Type);
        $this->assertTrue($markers['target'][1] instanceof Marker);
        
        
        
        $this->assertTrue($metadata->hasMethodMarker($default,"name"));
        $this->assertFalse($metadata->hasMethodMarker($this->_marker("Target"),"name"));
        
        $this->assertTrue($metadata->hasMethodMarker($default,"target"));
        $this->assertFalse($metadata->hasMethodMarker($this->_marker("Target"),"target"));
        
        
        $markers = $metadata->getMethodMarkers("name");
        $this->assertEquals(1, sizeof($markers));
        $this->assertTrue($markers[0] instanceof DefaultValue);
        
        
        $markers = $metadata->getMethodMarkers("target");
        $this->assertEquals(2, sizeof($markers));
        $this->assertTrue($markers[0] instanceof DefaultValue);
        $this->assertTrue($markers[1] instanceof Type);
        
        
        $defaultValue = $metadata->getMethodMarker($default,'name');
        $this->assertTrue($defaultValue instanceof DefaultValue);
        $this->assertEquals($defaultValue->value, "Foo Value");
        
        $defaultValue = $metadata->getMethodMarker($default,'target');
        $this->assertTrue($defaultValue instanceof DefaultValue);
        $this->assertTrue($defaultValue->value instanceof Target);
        $this->assertEquals($defaultValue->value->value, Target::TARGET_ALL);
    }

}