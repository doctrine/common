<?php

namespace Doctrine\Tests\Common\Annotations\Marker;

use Doctrine\Tests\Common\Annotations\Fixtures\Annotation\MarkedAnnotation;
use Doctrine\Common\Annotations\Marker\Strategy\MarkerStrategy;
use Doctrine\Common\Annotations\Marker\Strategy\RequiredStrategy;
use Doctrine\Common\Annotations\Marker\Annotation\Required;
use Doctrine\Common\Annotations\Marker\AnnotationMarkers;
use Doctrine\Common\Annotations\AnnotationReader;
use Doctrine\Common\Annotations\AnnotationException;
use Doctrine\Common\Annotations\Marker\Annotation\Target;

class RequiredTest extends \PHPUnit_Framework_TestCase
{
  
    /**
     * @group Marker
     */
    public function testRequired()
    {
        $markers    = new AnnotationMarkers(new \ReflectionClass('Doctrine\Tests\Common\Annotations\Fixtures\Annotation\MarkedAnnotation'), new AnnotationReader());
        $marker     = $markers->getPropertyMarker('Doctrine\Common\Annotations\Marker\Annotation\Required', 'data');
        $class      = new \ReflectionClass('Doctrine\Tests\Common\Annotations\Fixtures\MarkedClassName');
        $annot      = new MarkedAnnotation();
        $strategy   = MarkerStrategy::factory($markers, $marker);
        
        $this->assertNotNull($strategy);
        $this->assertTrue($strategy instanceof MarkerStrategy);
        $this->assertTrue($strategy instanceof RequiredStrategy);
        
        $this->assertNull($annot->data);
        
        try {
            $strategy->run($class, $annot);
            $this->fail();
        } catch (AnnotationException $exc) {
            $this->assertEquals($exc->getMessage(),'[Semantical Error] Property "data" can not be null. "Doctrine\Tests\Common\Annotations\Fixtures\MarkedClassName" at "@MarkedAnnotation".');
        }
    }


}