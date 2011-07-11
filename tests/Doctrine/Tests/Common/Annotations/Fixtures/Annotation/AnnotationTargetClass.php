<?php

namespace Doctrine\Tests\Common\Annotations\Fixtures\Annotation;


/**
 * @Marked
 * @Annotation
 * @Target("CLASS")
 */
final class AnnotationTargetClass
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