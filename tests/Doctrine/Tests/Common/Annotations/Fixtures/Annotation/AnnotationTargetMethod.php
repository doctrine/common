<?php

namespace Doctrine\Tests\Common\Annotations\Fixtures\Annotation;


/**
 * @Target("METHOD")
 * @Annotation
 * @Marked
 */
final class AnnotationTargetMethod
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