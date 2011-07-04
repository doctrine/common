<?php

namespace Doctrine\Tests\Common\Annotations\Fixtures;

use Doctrine\Tests\Common\Annotations\Fixtures\Annotation\AnnnotedAnnotation;
use Doctrine\Tests\Common\Annotations\Fixtures\Annotation\MarkedAnnotation;

/**
 * @AnnnotedAnnotation("Some data")
 */
class MarkedClassName
{

    /**
     * @MarkedAnnotation("Some data")
     */
    public $foo;
    
    
    /**
     * @MarkedAnnotation(name="Some name")
     */
    public $name;
    
    /**
     * @MarkedAnnotation("Some data",name="Some name")
     */
    public function someFunction()
    {
        
    }

}