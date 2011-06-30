<?php

namespace Doctrine\Tests\Common\Annotations\Fixtures\Annotation;

use Doctrine\Common\Annotations\Annotation\Annotation;

interface MyAnnotation extends Annotation
{

    function name();

    function data();
}