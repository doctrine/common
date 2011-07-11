<?php

namespace Doctrine\Tests\Common\Annotations\Marker;

use Doctrine\Tests\Common\Annotations\Fixtures\Annotation\MarkedAnnotation;
use Doctrine\Common\Annotations\Marker\Strategy\DefaultValueStrategy;
use Doctrine\Common\Annotations\Marker\Strategy\MarkerStrategy;
use Doctrine\Common\Annotations\Marker\Annotation\DefaultValue;
use Doctrine\Common\Annotations\Marker\AnnotationMarkers;
use Doctrine\Common\Annotations\Marker\Annotation\Target;

class DefaltValueStrategyTest extends \PHPUnit_Framework_TestCase
{
    
    /**
     * @group Marker
     */
    public function testNullValue()
    {
        $markers    = new AnnotationMarkers(new \ReflectionClass('Doctrine\Tests\Common\Annotations\Fixtures\Annotation\MarkedAnnotation'));
        $marker     = $markers->getPropertyMarker('Doctrine\Common\Annotations\Marker\Annotation\DefaultValue', 'name');
        $class      = new \ReflectionClass('Doctrine\Tests\Common\Annotations\Fixtures\MarkedClassName');
        $annot      = new MarkedAnnotation();
        $strategy   = MarkerStrategy::factory($markers, $marker);
        
        $this->assertNotNull($strategy);
        $this->assertTrue($strategy instanceof MarkerStrategy);
        $this->assertTrue($strategy instanceof DefaultValueStrategy);
        
        $this->assertNull($annot->name);
        
        $strategy->run($class, $annot);
        
        $this->assertEquals($annot->name, "Foo Value");
    }
    
    /**
     * @group Marker
     */
    public function testNotNullValue()
    {
        $markers        = new AnnotationMarkers(new \ReflectionClass('Doctrine\Tests\Common\Annotations\Fixtures\Annotation\MarkedAnnotation'));
        $marker         = $markers->getPropertyMarker('Doctrine\Common\Annotations\Marker\Annotation\DefaultValue', 'name');
        $class          = new \ReflectionClass('Doctrine\Tests\Common\Annotations\Fixtures\MarkedClassName');
        $annot          = new MarkedAnnotation();
        $strategy       = MarkerStrategy::factory($markers, $marker);
        $annot->name    = "Some name";
        
        $this->assertNotNull($strategy);
        $this->assertTrue($strategy instanceof MarkerStrategy);
        $this->assertTrue($strategy instanceof DefaultValueStrategy);
        
        $this->assertEquals($annot->name, "Some name");
        
        $strategy->run($class, $annot);
        
        $this->assertEquals($annot->name, "Some name");
    }

}