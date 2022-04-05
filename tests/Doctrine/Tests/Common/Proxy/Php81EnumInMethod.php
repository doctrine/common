<?php

namespace Doctrine\Tests\Common\Proxy;

enum Letter : string {
    case A = 'a';
    case B = 'b';
}

class Php81EnumInMethod
{
    public int $id;

    public function getLetterValue(Letter $enum = Letter::B): string
    {
        return $enum->value;
    }
}
