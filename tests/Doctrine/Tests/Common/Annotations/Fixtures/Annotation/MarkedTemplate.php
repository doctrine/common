<?php
namespace Doctrine\Tests\Common\Annotations\Fixtures\Annotation;

/**
 * @Marked
 * @Annotation
 * @Target("METHOD")
 */
class MarkedTemplate
{
    /**
     * @Type("string", nullable = true)
     */
    private $name;
}