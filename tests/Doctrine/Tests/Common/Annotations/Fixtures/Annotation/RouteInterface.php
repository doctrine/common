<?php

namespace Doctrine\Tests\Common\Annotations\Fixtures\Annotation;

use Doctrine\Common\Annotations\Proxy\Proxyable;
use Doctrine\Common\Annotations\Marker\Marked;
use Doctrine\Common\Annotations\Marker\Annotation\Target;
use Doctrine\Common\Annotations\Marker\Annotation\Type;

/**
 * @Target({"METHOD", "CLASS"})
 */
interface RouteInterface extends Proxyable, Marked
{

    /**
     * @Type("string", nullable = false)
     */
    public function pattern();

    /**
     * @Type("string", nullable = false)
     */
    public function name();
}