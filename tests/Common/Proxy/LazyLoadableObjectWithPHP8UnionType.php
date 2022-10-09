<?php

namespace Doctrine\Tests\Common\Proxy;

class LazyLoadableObjectWithPHP8UnionType
{
    private int|string|null $identifierFieldUnionType = null;

    public function getIdentifierFieldUnionType(): int|string|null
    {
        return $this->identifierFieldUnionType;
    }
}
