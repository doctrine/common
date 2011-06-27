<?php

namespace Doctrine\Tests\Common\Annotations\Fixtures\Annotation;

use Doctrine\Common\Annotations\Annotation\DefaultValue;
use Doctrine\Common\Annotations\Annotation\Annotation;
use Doctrine\Common\Annotations\Annotation\Target;
use Doctrine\Common\Annotations\Annotation\Type;

/**
 * @Target("CLASS")
 */
interface AnnnotedAnnotation
{
    function data();

    /**
     *@DefaultValue("Foo Value")
     */
    function name();

    /**
     * @DefaultValue("@Target("ALL")")
     * @Type("Doctrine\Common\Annotations\Annotation\Target", nullable = true)
     */
    function target();
}