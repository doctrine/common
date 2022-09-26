<?php

namespace Doctrine\Tests\Common\Proxy;

class PHP81NeverType
{
    public function __get($name) : never
    {
        die('Please do not use __get');
    }

    public function __set($name, $value) : never
    {
        die('Please do not use __set');
    }

    public function finishHim() : never
    {
        die('Finish him');
    }
}
