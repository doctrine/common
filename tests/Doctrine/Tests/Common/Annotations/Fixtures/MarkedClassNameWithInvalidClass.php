<?php

namespace Doctrine\Tests\Common\Annotations\Fixtures;

use Doctrine\Tests\Common\Annotations\Fixtures\Annotation\AnnotationTargetMethod;

/**
 * @AnnotationTargetMethod("Some data")
 */
class MarkedClassNameWithInvalidClass
{
    
    /**
     * @AnnotationTargetMethod("Bar")
     */
    public $foo;
}