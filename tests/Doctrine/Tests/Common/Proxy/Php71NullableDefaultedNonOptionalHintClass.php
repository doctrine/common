<?php

namespace Doctrine\Tests\Common\Proxy;

/**
 * Test PHP 7.1 compatibility of nullable type hints generation on a non-optional hinted parameter that is nullable,
 * yet has a default parameter
 *
 * @see https://github.com/doctrine/common/issues/751
 */
class Php71NullableDefaultedNonOptionalHintClass
{
    public function midSignatureNullableParameter(?string $param = null, $secondParam)
    {
    }

    public function midSignatureNotNullableHintedParameter(?string $param = 'foo', $secondParam)
    {
    }
}
