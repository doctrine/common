<?php

namespace Doctrine\Tests\Common\Proxy;

use stdClass;

class Php8UnionTypes
{
    public string|int $foo;

    public function setValue(stdClass|array $value) : bool|float
    {
    }
}
