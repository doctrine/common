<?php

namespace Doctrine\Tests\Common\Annotations\Proxy;

use Doctrine\Tests\Common\Annotations\Fixtures\Annotation\AnnnotedAnnotation;
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
        $target         = new ClassNameWithMarkedAnnotations();
        $annotations    = $reader->getClassAnnotations($class);
        
        $commamnds      = $maker->runMarkers($annotations, $target);
        
        
        
        echo sizeof($commamnds) . "\n\n";
        //echo get_class($commamnds[0]) . "\n\n";
        die;
    }
    
   
    
}



/**
 * @AnnnotedAnnotation()
 */
class ClassNameWithMarkedAnnotations
{
    
    private $foo;
}