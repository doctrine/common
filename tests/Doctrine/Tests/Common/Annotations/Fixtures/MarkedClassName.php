<?php

namespace Doctrine\Tests\Common\Annotations\Fixtures;

use Doctrine\Tests\Common\Annotations\Fixtures\Annotation\AnnotationTargetClass;
use Doctrine\Tests\Common\Annotations\Fixtures\Annotation\MarkedAnnotation;
use Doctrine\Tests\Common\Annotations\Fixtures\Annotation\MarkerdAnnotationInterface;

/**
 * @AnnotationTargetClass("Some data")
 */
class MarkedClassName
{

    /**
     * @MarkedAnnotation("Some data")
     */
    public $foo;
    
    
    /**
     * @MarkedAnnotation("Some data",name="Some name")
     */
    public $name;
    
    /**
     * @MarkedAnnotation("Some data",name="Some name")
     */
    public function someFunction()
    {
        
    }
    
    
    /**
     * @MarkerdAnnotationInterface("Some data",name="Some name")
     */
    public $interfaceMarked;

}