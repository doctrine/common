<?php

namespace Doctrine\Tests\Common\Annotations\Fixtures\Annotation;
use Doctrine\Common\Annotations\Proxy\AbstractProxy;

class MyAnnotationImplInvalid implements MyAnnotation
{

    private $name;
    private $data;
    public function name()
    {
        return $this->name;
    }

    public function data()
    {
        return $this->data;
    }

}