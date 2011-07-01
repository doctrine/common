<?php

namespace Doctrine\Tests\Common\Annotations\Proxy;

use Doctrine\Tests\Common\Annotations\Fixtures\Annotation\AnnnotedAnnotation;
use Doctrine\Tests\Common\Annotations\Fixtures\Annotation\AnnnotedAnnotationClass;
use Doctrine\Common\Annotations\Marker\Strategy\TargetStrategy;
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
        $this->markTestIncomplete();
        
        $class          = new ReflectionClass(__NAMESPACE__.'\ClassNameWithMarkedAnnotations');
        $reader         = new AnnotationReader();
        $maker          = new ClassMarker($class,$reader);
        $target         = $class->newInstance();
        $annotations    = $reader->getClassAnnotations($class);
        
        
        $commamnds      = $maker->runMarkers($annotations, $target);
        
        
        $this->assertEquals(sizeof($annotations), 1);
        $this->assertEquals(sizeof($commamnds), 4);
        
        
        
        $this->assertTrue($commamnds[0] instanceof TargetStrategy);
        //$this->assertEquals($commamnds[0]->getMarker(), $annotations[0]);
        
        
        
        
        echo sizeof($commamnds) . "\n\n";
        echo get_class($commamnds[0]) . "\n\n";
    }
    
   
    
}



/**
 * @AnnnotedAnnotation()
 */
class ClassNameWithMarkedAnnotations
{
    
    /**
     * @AnnnotedAnnotation()
     */
    private $foo;
}