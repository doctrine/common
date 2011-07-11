<?php
namespace Doctrine\Tests\Common\Annotations\Fixtures\Annotation;

/**
 * @Target("METHOD")
 * @Annotation
 * @Marked
 */
class MarkedTemplate
{
    /**
     * @Type("string", nullable = true)
     */
    private $name;
}