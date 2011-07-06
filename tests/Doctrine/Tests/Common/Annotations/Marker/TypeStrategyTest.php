<?php

namespace Doctrine\Tests\Common\Annotations\Marker;

use Doctrine\Tests\Common\Annotations\Fixtures\Annotation\MarkedAnnotation;
use Doctrine\Common\Annotations\Marker\Strategy\TypeStrategy;
use Doctrine\Common\Annotations\Marker\Strategy\MarkerStrategy;
use Doctrine\Common\Annotations\Marker\Annotation\Type;
use Doctrine\Common\Annotations\Marker\AnnotationMarkers;
use Doctrine\Common\Annotations\AnnotationReader;
use Doctrine\Common\Annotations\AnnotationException;
use Doctrine\Common\Annotations\Marker\Annotation\Target;

class TypeStrategyTest extends \PHPUnit_Framework_TestCase
{
    
     /**
     * @group Marker
     */
    public function testTypeValue()
    {
        $markers    = new AnnotationMarkers(new \ReflectionClass('Doctrine\Tests\Common\Annotations\Fixtures\Annotation\MarkedAnnotation'), new AnnotationReader());
        $marker     = $markers->getPropertyMarker('Doctrine\Common\Annotations\Marker\Annotation\Type', 'target');
        $class      = new \ReflectionClass('Doctrine\Tests\Common\Annotations\Fixtures\MarkedClassName');
        $annot      = new MarkedAnnotation();
        $strategy   = MarkerStrategy::factory($markers, $marker);
        
        $this->assertNotNull($strategy);
        $this->assertTrue($strategy instanceof MarkerStrategy);
        $this->assertTrue($strategy instanceof TypeStrategy);
        
        try {
            $ref = new \ReflectionClass($annot);
            $ref = $ref->getProperty('target');
            $ref->setAccessible(true);
            $ref->setValue($annot, "Some string value");
            $ref->setAccessible(false);
            
            $strategy->run($class, $annot);
            
            $this->fail();
        } catch (AnnotationException $exc) {
            $this->assertEquals($exc->getMessage(),'[Semantical Error] Property "target" must be an instance of "Doctrine\Common\Annotations\Marker\Annotation\Target", "string" given. "Doctrine\Tests\Common\Annotations\Fixtures\MarkedClassName" at "@MarkedAnnotation".');
        }
    }
    
    
    /**
     * @group Marker
     */
    public function testNullValue()
    {
        $markers    = new AnnotationMarkers(new \ReflectionClass('Doctrine\Tests\Common\Annotations\Fixtures\Annotation\MarkedAnnotation'), new AnnotationReader());
        $marker     = $markers->getPropertyMarker('Doctrine\Common\Annotations\Marker\Annotation\Type', 'target');
        $class      = new \ReflectionClass('Doctrine\Tests\Common\Annotations\Fixtures\MarkedClassName');
        $annot      = new MarkedAnnotation();
        $strategy   = MarkerStrategy::factory($markers, $marker);
        
        $this->assertNotNull($strategy);
        $this->assertTrue($strategy instanceof MarkerStrategy);
        $this->assertTrue($strategy instanceof TypeStrategy);
        
        $this->assertEquals($marker->nullable,true);
        
        $this->assertNull($annot->target);
        $strategy->run($class, $annot);
        
        try {
            $ref = new \ReflectionClass('Doctrine\Common\Annotations\Marker\Annotation\Type');
            $ref = $ref->getProperty('nullable');
            $ref->setAccessible(true);
            $ref->setValue($marker, false);
            $ref->setAccessible(false);
            
            $strategy->run($class, $annot);
            
            $this->fail();
        } catch (AnnotationException $exc) {
            $this->assertEquals($exc->getMessage(),'[Semantical Error] Property "target" must be an instance of "Doctrine\Common\Annotations\Marker\Annotation\Target", "NULL" given. "Doctrine\Tests\Common\Annotations\Fixtures\MarkedClassName" at "@MarkedAnnotation".');
        }
    }


}