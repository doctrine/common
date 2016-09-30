<?php

namespace Doctrine\Tests\Common\Proxy;

/**
 * Test PHP 7.1 nullable type hints / return types class.
 */
class NullableTypeHintsClass
{
    public function nullableTypeHintInt(?int $param)
    {
    }

    public function nullableTypeHintObject(?\stdClass $param)
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

    public function notNullableTypeHintWithDefaultNull(int $param = null)
    {
    }

    public function returnsNullableInt() : ?int
    {
    }

    public function returnsNullableObject() : ?\stdClass
    {
    }

    public function returnsNullableSelf() : ?self
    {
    }
}
