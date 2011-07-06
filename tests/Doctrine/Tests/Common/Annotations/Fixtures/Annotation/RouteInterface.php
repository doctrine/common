<?php

namespace Doctrine\Tests\Common\Annotations\Fixtures\Annotation;
use Doctrine\Common\Annotations\Proxy\Proxyable;
use Doctrine\Common\Annotations\Marker\Marked;

interface RouteInterface extends Proxyable, Marked
{
    public function pattern();
    public function name();
}