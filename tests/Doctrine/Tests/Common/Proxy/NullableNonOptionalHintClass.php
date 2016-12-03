<?php

namespace Doctrine\Tests\Common\Proxy;

/**
 * Test PHP 7.0 compatibility of nullable type hints generation on a non-optional hinted parameter that is nullable
 *
 * @see https://github.com/doctrine/common/issues/751
 */
class NullableNonOptionalHintClass
{
    public function midSignatureNullableParameter(\stdClass $param = null, $secondParam)
    {
    }

    public function midSignatureNotNullableHintedParameter(string $param = 'foo', $secondParam)
    {
    }
}
