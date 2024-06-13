<?php

namespace Doctrine\Tests\Common\Proxy;

class LazyLoadableObjectWithPHP81EnumIntType
{
    private LazyLoadableObjectWithPHP81EnumIntTypeIdentfier $identifierFieldEnumIntType;

    public function getIdentifierFieldEnumIntType(): LazyLoadableObjectWithPHP81EnumIntTypeIdentfier
    {
        return $this->identifierFieldEnumIntType;
    }

    public static function getFooIdentifier(): LazyLoadableObjectWithPHP81EnumIntTypeIdentfier
    {
        return LazyLoadableObjectWithPHP81EnumIntTypeIdentfier::FOO;
    }
}

enum LazyLoadableObjectWithPHP81EnumIntTypeIdentfier: int
{
    case FOO = 1;
    case BAR = 2;
}
