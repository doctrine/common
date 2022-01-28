<?php

namespace Doctrine\Tests\Common\Proxy;

enum YesOrNo : int {
    case YES = 1;
    case NO = 0;
}

class Php81EnumPublicPropertyType
{
    public int $id;
    public YesOrNo $isEnum = YesOrNo::YES;
}
