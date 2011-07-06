<?php

namespace Doctrine\Tests\Common\Annotations\Fixtures\Annotation;

use Doctrine\Common\Annotations\Marker\Annotation\Target;
use Doctrine\Common\Annotations\Marker\Annotation\Type;
use Doctrine\Common\Annotations\Marker\Marked;
use Doctrine\Common\Annotations\Annotation\Annotation;

/**
 * @Target({"METHOD", "CLASS"})
 */
final class Route implements Marked, Annotation
{
    
    public function __construct(array $values)
    {
        $this->pattern = $values['value'];
        $this->name    = isset($values['name'])? $values['name'] : null;
    }
    
    /**
     * @Type("string", nullable = false)
     */
    private $pattern;
    
    /**
     * @Type("string", nullable = true)
     */
    private $name;
}