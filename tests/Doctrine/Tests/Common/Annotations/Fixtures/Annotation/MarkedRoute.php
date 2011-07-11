<?php

namespace Doctrine\Tests\Common\Annotations\Fixtures\Annotation;

/**
 * @Target({"METHOD", "CLASS"})
 * @Annotation
 * @Marked
 * @LALALA
 */
final class MarkedRoute
{
    
    /**
     * @Type("string", nullable = false)
     */
    private $pattern;
    
    /**
     * @Type("string", nullable = true)
     */
    private $name;
}