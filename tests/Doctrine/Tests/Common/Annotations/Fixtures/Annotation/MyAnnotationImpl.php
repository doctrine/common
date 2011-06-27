<?php

namespace Doctrine\Tests\Common\Annotations\Fixtures\Annotation;
use Doctrine\Common\Annotations\Proxy\AbstractProxy;

class MyAnnotationImpl extends AbstractProxy implements MyAnnotation
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