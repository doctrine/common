<?php

namespace Doctrine\Tests\Common\Proxy;

/**
 * Test asset class
 */
class InvalidReturnTypeClass
{
    /**
     * @return InvalidReturnType (non existing class return type)
     */
    public function invalidReturnTypeMethod() : InvalidReturnType
    {

    }
}
