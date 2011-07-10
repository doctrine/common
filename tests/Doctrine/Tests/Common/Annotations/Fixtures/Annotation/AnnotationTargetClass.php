<?php

namespace Doctrine\Tests\Common\Annotations\Fixtures\Annotation;

use Doctrine\Common\Annotations\Marker\Annotation\DefaultValue;
use Doctrine\Common\Annotations\Marker\Annotation\Target;
use Doctrine\Common\Annotations\Marker\Annotation\Type;
use Doctrine\Common\Annotations\Marker\Annotation\Required;
use Doctrine\Common\Annotations\Marker\Annotation\Marked;
use Doctrine\Common\Annotations\Annotation;

/**
 * @Marked()
 * @Target("CLASS")
 * @Annotation()
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