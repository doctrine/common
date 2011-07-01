<?php

namespace Doctrine\Tests\Common\Annotations\Fixtures\Annotation;

use Doctrine\Common\Annotations\Annotation\Annotation;
use Doctrine\Common\Annotations\Marker\Annotation\DefaultValue;
use Doctrine\Common\Annotations\Marker\Annotation\Target;
use Doctrine\Common\Annotations\Marker\Annotation\Type;

/**
 * @Target("ALL")
 */
interface AnnnotedAnnotation extends Annotation
{
    function data();

    /**
     *@DefaultValue("Foo Value")
     */
    function name();

    /**
     * @DefaultValue(@Target("ALL"))
     * @Type("Doctrine\Common\Annotations\Annotation\Target", nullable = true)
     */
    function target();
}