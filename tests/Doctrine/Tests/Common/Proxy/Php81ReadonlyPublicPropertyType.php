<?php

namespace Doctrine\Tests\Common\Proxy;

class Php81ReadonlyPublicPropertyType
{
    public readonly string $readable;
    public string $writeable = 'default';

    public function __construct(
        public readonly string $id,
    ) {}
}
