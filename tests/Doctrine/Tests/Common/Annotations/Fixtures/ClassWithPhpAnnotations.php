<?php

namespace Doctrine\Tests\Common\Annotations\Fixtures;

use Doctrine\Tests\Common\Annotations\Fixtures\AnnotationTargetClass;
use Doctrine\Tests\Common\Annotations\Fixtures\AnnotationTargetAll;
use Doctrine\Tests\Common\Annotations\Fixtures\AnnotationTargetPropertyMethod;

/**
 * @AnnotationTargetClass
 */
class ClassWithPhpAnnotations
{

    /**
     * @var integer
     */
    public $foo;
    
    
    /**
     * @var bool|string
     * @AnnotationTargetPropertyMethod("Some Value")
     */
    public $bar;

    /**
     * @var array<float>
     * @AnnotationTargetAll
     */
    public $foobar;
    

    /**
     * @param float $param
     * @return boolean
     */
    public function someFunction($param)
    {
       return true;
    }

}