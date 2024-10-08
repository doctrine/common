<?php

namespace Doctrine\Tests\Common\Proxy;

use stdClass;

class Php8UnionTypes
{
    public string|int $foo;

    public string|int|null $bar;

    public function setValue(stdClass|array $value) : bool|float
    {
        return true;
    }

    public function setNullableValue(stdClass|array|null $value) : bool|float|null
    {
        return true;
    }

    public function setNullableValueDefaultNull(stdClass|array|null $value = null) : bool|float|null
    {
        return true;
    }
}
