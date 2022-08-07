<?php

declare(strict_types=1);

namespace Doctrine\Tests\Common\Proxy;

class PHP81NewInInitializers
{
    public function onlyInitializer($foo = new \stdClass()): void
    {

    }

    public function typed(\DateTimeInterface $foo = new \DateTimeImmutable('now')): void
    {

    }

    public function arrayInDefault(array $foo = [new \DateTimeImmutable('2022-08-22 16:20', new \DateTimeZone('Europe/Warsaw'))]): void
    {

    }
}
