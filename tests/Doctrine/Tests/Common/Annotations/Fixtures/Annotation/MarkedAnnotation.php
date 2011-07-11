<?php

namespace Doctrine\Tests\Common\Annotations\Fixtures\Annotation;

/**
 * @Target("ALL")
 * @Annotation
 * @Marked
 */
final class MarkedAnnotation
{
    /**
     * @Required()
     */
    public $data;
    /**
     * @DefaultValue("Foo Value")
     */
    public $name;
    /**
     * @DefaultValue(@Target("ALL"))
     * @Type("Doctrine\Common\Annotations\Marker\Annotation\Target", nullable = true)
     */
    public $target;
}