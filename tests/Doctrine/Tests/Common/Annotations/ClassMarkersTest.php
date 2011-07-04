<?php

namespace Doctrine\Tests\Common\Annotations\Proxy;

use Doctrine\Tests\Common\Annotations\Fixtures\Annotation\AnnnotedAnnotation;
use Doctrine\Tests\Common\Annotations\Fixtures\Annotation\AnnnotedAnnotationClass;

use Doctrine\Common\Annotations\Marker\Annotation\Target;
use Doctrine\Common\Annotations\Marker\Strategy\TypeStrategy;
use Doctrine\Common\Annotations\Marker\Strategy\TargetStrategy;
use Doctrine\Common\Annotations\Marker\Strategy\DefaultValueStrategy;

use Doctrine\Common\Annotations\AnnotationException;
use Doctrine\Common\Annotations\AnnotationReader;
use Doctrine\Common\Annotations\ClassMarker;
use \ReflectionClass;

class ClassMarkersTest extends \PHPUnit_Framework_TestCase
{
    
     /**
     * @group Marker
     */
    public function testRunMarkers()
    {
        $reader         = new AnnotationReader();
        
        $class          = new ReflectionClass(__NAMESPACE__.'\MarkedClassNameWithInvalidProperty');
        $maker          = new ClassMarker($class,$reader);
        $annotations    = $reader->getClassAnnotations($class);
        
        try {
            $maker->runMarkers($annotations);
            $this->fail();
        } catch (AnnotationException $exc) {
            $this->assertEquals($exc->getMessage(),'[Semantical Error] Annotation "Doctrine\Tests\Common\Annotations\Fixtures\Annotation\AnnnotedAnnotation" can not be used at property "foo"');
        }
        
        
        $class          = new ReflectionClass(__NAMESPACE__.'\MarkedClassNameWithInvalidMethod');
        $maker          = new ClassMarker($class,$reader);
        $annotations    = $reader->getMethodAnnotations($class->getMethod('functionName'));
        
        try {
            $maker->runMarkers($annotations);
            $this->fail();
        } catch (AnnotationException $exc) {
            $this->assertEquals($exc->getMessage(),'[Semantical Error] Annotation "Doctrine\Tests\Common\Annotations\Fixtures\Annotation\AnnnotedAnnotation" can not be used at method "functionName"');
        }


        
        $class          = new ReflectionClass(__NAMESPACE__.'\MarkedClassName');
        $maker          = new ClassMarker($class,$reader);
        $annotations    = $reader->getClassAnnotations($class);

        $commamnds      = $maker->runMarkers($annotations);
        
        $this->assertEquals(sizeof($commamnds), 4);
        
    }
    
    
     /**
     * @group Marker
     */
    public function testMarkersStrategy()
    {
        $reader         = new AnnotationReader();
        $class          = new ReflectionClass(__NAMESPACE__.'\MarkedClassName');
        $maker          = new ClassMarker($class,$reader);
        $annotations    = $reader->getClassAnnotations($class);

        $commamnds = $maker->runMarkers($annotations);
        
        $this->assertTrue($commamnds[0] instanceof TargetStrategy);
        $this->assertTrue($commamnds[1] instanceof DefaultValueStrategy);
        $this->assertTrue($commamnds[2] instanceof DefaultValueStrategy);
        $this->assertTrue($commamnds[3] instanceof TypeStrategy);

        
        $this->assertTrue($annotations[0] instanceof AnnnotedAnnotation);
        $this->assertEquals($annotations[0]->name,"Foo Value");
        $this->assertEquals($annotations[0]->target->value, Target::TARGET_ALL);
    }
    
   
    
}




/**
 * @AnnnotedAnnotation()
 */
class MarkedClassName
{
}



/**
 * @AnnnotedAnnotation()
 */
class MarkedClassNameWithInvalidProperty
{
    
    /**
     * @AnnnotedAnnotation()
     */
    public $foo;
}

/**
 * @AnnnotedAnnotation()
 */
class MarkedClassNameWithInvalidMethod
{
    
    /**
     * @AnnnotedAnnotation()
     */
    public function functionName($param)
    {
        
    }
}