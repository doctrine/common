<?php

namespace Doctrine\Tests\Common\Annotations\Fixtures\Annotation;

use Doctrine\Common\Annotations\Annotation\Annotation;
use Doctrine\Common\Annotations\Marker\Annotation\DefaultValue;
use Doctrine\Common\Annotations\Marker\Annotation\Target;
use Doctrine\Common\Annotations\Marker\Annotation\Type;

/**
 * @Target("CLASS")
 */
final class AnnnotedAnnotation implements Annotation
{

    public final function __construct(array $data)
    {
        foreach ($data as $key => $value) {
            $this->$key = $value;
        }
    }

    public $data;
    /**
     * @DefaultValue("Foo Value")
     */
    public $name;
    /**
     * @DefaultValue(@Target("ALL"))
     * @Type("Doctrine\Common\Annotations\Annotation\Target", nullable = true)
     */
    public $target;
}