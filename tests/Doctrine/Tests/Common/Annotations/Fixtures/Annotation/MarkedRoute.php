<?php

namespace Doctrine\Tests\Common\Annotations\Fixtures\Annotation;

/**
 * @Marked
 * @Annotation
 * @Target({"METHOD", "CLASS"})
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