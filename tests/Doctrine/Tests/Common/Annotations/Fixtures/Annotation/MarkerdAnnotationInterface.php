<?php

namespace Doctrine\Tests\Common\Annotations\Fixtures\Annotation;

use Doctrine\Common\Annotations\Annotation\Annotation;
use Doctrine\Common\Annotations\Proxy\Decorable;
use Doctrine\Common\Annotations\Proxy\Proxyable;
use Doctrine\Common\Annotations\Marker\Annotation\DefaultValue;
use Doctrine\Common\Annotations\Marker\Annotation\Target;
use Doctrine\Common\Annotations\Marker\Annotation\Type;
use Doctrine\Common\Annotations\Marker\Annotation\Required;
use Doctrine\Common\Annotations\Marker\Marked;


/**
 * @Target("ALL")
 */
interface MarkerdAnnotationInterface extends Annotation, Decorable, Proxyable, Marked
{
    /**
     * @Required()
     */
    function data();

    /**
     * @DefaultValue("Foo Value")
     */
    function name();

    /**
     * @DefaultValue(@Target("ALL"))
     * @Type("Doctrine\Common\Annotations\Marker\Annotation\Target", nullable = true)
     */
    function target();
}