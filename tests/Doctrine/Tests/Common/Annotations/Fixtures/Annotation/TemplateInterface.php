<?php

namespace Doctrine\Tests\Common\Annotations\Fixtures\Annotation;

use Doctrine\Common\Annotations\Proxy\Proxyable;

interface TemplateInterface extends Proxyable
{
    public function name();
}