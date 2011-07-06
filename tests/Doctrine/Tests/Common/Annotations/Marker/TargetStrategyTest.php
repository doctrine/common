<?php

namespace Doctrine\Tests\Common\Annotations\Marker;

use Doctrine\Tests\Common\Annotations\Fixtures\Annotation\AnnnotedAnnotation;
use Doctrine\Tests\Common\Annotations\Fixtures\Annotation\MarkedAnnotation;
use Doctrine\Common\Annotations\Marker\AnnotationMarkers;
use Doctrine\Common\Annotations\Marker\Strategy\TargetStrategy;
use Doctrine\Common\Annotations\Marker\Strategy\MarkerStrategy;
use Doctrine\Common\Annotations\AnnotationReader;
use Doctrine\Common\Annotations\AnnotationException;
use \ReflectionClass;

class TargetStrategyTest extends \PHPUnit_Framework_TestCase
{


    /**
     * @group Marker
     */
    public function testInvalidTargetClass()
    {
        $markers    = new AnnotationMarkers(new \ReflectionClass('Doctrine\Tests\Common\Annotations\Fixtures\Annotation\AnnotationTargetClass'), new AnnotationReader());
        $class      = new \ReflectionClass('Doctrine\Tests\Common\Annotations\Fixtures\MarkedClassNameWithInvalidMethod');
        $annotation = new MarkedAnnotation();
        
        $marker     = $markers->getClassMarker('Doctrine\Common\Annotations\Marker\Annotation\Target');
        $target     = $class->getMethod('functionName');
        $strategy   = MarkerStrategy::factory($markers, $marker);
        
        $this->assertNotNull($strategy);
        $this->assertTrue($strategy instanceof MarkerStrategy);
        $this->assertTrue($strategy instanceof TargetStrategy);
        
        try {
            $strategy->run($target, $annotation);
            $this->fail();
        } catch (AnnotationException $exc) {
            $this->assertEquals($exc->getMessage(),'[Semantical Error] Declaration of "@AnnotationTargetClass" is not compatible with annotation target [CLASS], "Doctrine\Tests\Common\Annotations\Fixtures\MarkedClassNameWithInvalidMethod" at method "functionName".');
        }
    }
    
    
     /**
     * @group Marker
     */
    public function testInvalidTargetProperty()
    {
        
        $markers    = new AnnotationMarkers(new \ReflectionClass('Doctrine\Tests\Common\Annotations\Fixtures\Annotation\AnnotationTargetClass'), new AnnotationReader());
        $class      = new \ReflectionClass('Doctrine\Tests\Common\Annotations\Fixtures\MarkedClassNameWithInvalidProperty');
        $annotation = new MarkedAnnotation();
        
        $marker     = $markers->getClassMarker('Doctrine\Common\Annotations\Marker\Annotation\Target');
        $target     = $class->getProperty('foo');
        $strategy   = MarkerStrategy::factory($markers, $marker);
        
        $this->assertNotNull($strategy);
        $this->assertTrue($strategy instanceof MarkerStrategy);
        $this->assertTrue($strategy instanceof TargetStrategy);
        
        try {
            $strategy->run($target, $annotation);
            $this->fail();
        } catch (AnnotationException $exc) {
            $this->assertEquals($exc->getMessage(),'[Semantical Error] Declaration of "@AnnotationTargetClass" is not compatible with annotation target [CLASS], "Doctrine\Tests\Common\Annotations\Fixtures\MarkedClassNameWithInvalidProperty" at property "foo".');
        }
        
    }
    
    
     /**
     * @group Marker
     */
    public function testInvalidTargetMethod()
    {
        
        $markers    = new AnnotationMarkers(new \ReflectionClass('Doctrine\Tests\Common\Annotations\Fixtures\Annotation\AnnotationTargetMethod'), new AnnotationReader());
        $class      = new \ReflectionClass('Doctrine\Tests\Common\Annotations\Fixtures\MarkedClassNameWithInvalidClass');
        $annotation = new MarkedAnnotation();
        
        $marker     = $markers->getClassMarker('Doctrine\Common\Annotations\Marker\Annotation\Target');
        $target     = $class;
        $strategy   = MarkerStrategy::factory($markers, $marker);
        
        $this->assertNotNull($strategy);
        $this->assertTrue($strategy instanceof MarkerStrategy);
        $this->assertTrue($strategy instanceof TargetStrategy);
        
        try {
            $strategy->run($target, $annotation);
            $this->fail();
        } catch (AnnotationException $exc) {
            $this->assertEquals($exc->getMessage(),'[Semantical Error] Declaration of "@AnnotationTargetMethod" is not compatible with annotation target [METHOD], at class "Doctrine\Tests\Common\Annotations\Fixtures\MarkedClassNameWithInvalidClass".');
        }
   
    }
    
    
     /**
     * @group Marker
     */
    public function testInvalidTargetPropertyMethod()
    {
        
        $markers    = new AnnotationMarkers(new \ReflectionClass('Doctrine\Tests\Common\Annotations\Fixtures\Annotation\AnnotationTargetPropertyMethod'), new AnnotationReader());
        $class      = new \ReflectionClass('Doctrine\Tests\Common\Annotations\Fixtures\MarkedClassNameWithInvalidTarget');
        $annotation = new MarkedAnnotation();
        
        $marker     = $markers->getClassMarker('Doctrine\Common\Annotations\Marker\Annotation\Target');
        $target     = $class;
        $strategy   = MarkerStrategy::factory($markers, $marker);
        
        $this->assertNotNull($strategy);
        $this->assertTrue($strategy instanceof MarkerStrategy);
        $this->assertTrue($strategy instanceof TargetStrategy);
        
        try {
            $strategy->run($target, $annotation);
            $this->fail();
        } catch (AnnotationException $exc) {
            $this->assertEquals($exc->getMessage(),'[Semantical Error] Declaration of "@AnnotationTargetPropertyMethod" is not compatible with annotation target [METHOD, PROPERTY], at class "Doctrine\Tests\Common\Annotations\Fixtures\MarkedClassNameWithInvalidTarget".');
        }
    }
    
    
    
    

}