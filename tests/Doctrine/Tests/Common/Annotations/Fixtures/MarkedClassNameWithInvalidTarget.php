<?php

namespace Doctrine\Tests\Common\Annotations\Fixtures;

use Doctrine\Tests\Common\Annotations\Fixtures\Annotation\AnnotationTargetPropertyMethod;

/**
 * @AnnotationTargetPropertyMethod("Some data")
 */
class MarkedClassNameWithInvalidTarget
{
    
    /**
     * @AnnotationTargetMethod(@AnnotationTargetPropertyMethod("Data"))
     */
    public $foo;
}