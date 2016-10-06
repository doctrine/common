<?php

namespace Doctrine\Tests\Common\Proxy;

/**
 * Test PHP 7 scalar type hints class.
 */
class ScalarTypeHintsClass
{
    public function singleTypeHint(string $param)
    {
    }

    public function multipleTypeHints(int $a, float $b, bool $c, string $d)
    {
    }

    public function combinationOfTypeHintsAndNormal(\stdClass $a, \Countable $b, $c, int $d)
    {
    }

    public function typeHintsWithVariadic(int ...$foo)
    {
    }

    public function withDefaultValue(int $foo = 123)
    {
    }

    public function withDefaultValueNull(int $foo = null)
    {
    }
}
