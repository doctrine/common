<?php
namespace Doctrine\Tests\Common\Annotations\Fixtures\Annotation;

use Doctrine\Common\Annotations\Marker\Annotation\Target;
use Doctrine\Common\Annotations\Marker\Annotation\Type;
use Doctrine\Common\Annotations\Marker\Marked;
use Doctrine\Common\Annotations\Annotation\Annotation;
use Doctrine\Common\Annotations\Proxy\Decorable;

/**
 * @Target("METHOD")
 */
class Template implements Annotation, Decorable, Marked
{
    /**
     * @Type("string", nullable = true)
     */
    private $name;
}