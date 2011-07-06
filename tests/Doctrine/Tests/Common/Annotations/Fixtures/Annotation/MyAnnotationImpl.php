<?php

namespace Doctrine\Tests\Common\Annotations\Fixtures\Annotation;

class MyAnnotationImpl implements MyAnnotation, \Doctrine\Common\Annotations\Proxy\Decorable
{

    private $name;
    private $data;

    public function __construct($data)
    {
        if(isset($data['value'])){
            $data['name'] = $data['value'];
            unset ($data['value']);
        }
        foreach ($data as $key => $value) {
            $this->$key = $value;
        }
    }
    
    public function name()
    {
        return $this->name;
    }

    public function data()
    {
        return $this->data;
    }

    public function __get($name)
    {
        if (!isset($this->$name))
        {
            throw new \BadMethodCallException(
                    sprintf("Unknown property \'%s\' on annotation \'%s\'.", $name, get_called_class())
            );
        }
        return $this->$name;
    }

}