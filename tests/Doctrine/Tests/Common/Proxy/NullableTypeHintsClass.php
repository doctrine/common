<?php

namespace Doctrine\Tests\Common\Proxy;

use stdClass;

/**
 * Test PHP 7.1 nullable type hints / return types class.
 */
class NullableTypeHintsClass
{
    public function nullableTypeHintInt(?int $param)
    {
    }

    public function nullableTypeHintObject(?stdClass $param)
    {
    }

    public function nullableTypeHintSelf(?self $param)
    {
    }

    public function nullableTypeHintWithDefault(?int $param = 123)
    {
    }

    public function nullableTypeHintWithDefaultNull(?int $param = null)
    {
    }

    public function notNullableTypeHintWithDefaultNull(?int $param = null)
    {
    }

    public function returnsNullableInt() : ?int
    {
        return null;
    }

    public function returnsNullableObject() : ?stdClass
    {
        return null;
    }

    public function returnsNullableSelf() : ?self
    {
        return null;
    }
}
