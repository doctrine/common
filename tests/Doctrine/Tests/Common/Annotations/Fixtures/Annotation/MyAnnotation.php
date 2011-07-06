<?php

namespace Doctrine\Tests\Common\Annotations\Fixtures\Annotation;

use Doctrine\Common\Annotations\Proxy\Proxyable;

interface MyAnnotation extends Proxyable
{

    function name();

    function data();
}