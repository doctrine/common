<?php

declare(strict_types=1);

namespace Doctrine\Tests\Common\Proxy;

use Doctrine\Tests\Common\Util\TestAsset\ConstProvider;

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

    public function scalarConstInDefault(string $foo = ConstProvider::FOO_SCALAR): void
    {

    }

    public function constInDefault(array $foo = ConstProvider::FOO): void
    {

    }

    public function globalEolInDefault(string $foo = \PHP_EOL): void
    {

    }

    public function specialCharacterInDefault(string $foo = "\n"): void
    {

    }
}
