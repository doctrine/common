<?php

namespace Doctrine\Tests\Common\Proxy;

class LazyLoadableObjectWithPHP81IntersectionType
{
    private \stdClass&\Stringable $identifierFieldIntersectionType;

    public function getIdentifierFieldIntersectionType(): \stdClass&\Stringable
    {
        return $this->identifierFieldIntersectionType;
    }
}
