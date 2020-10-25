<?php

namespace Doctrine\Tests\Common\Util\TestAsset;

final class ChildClass extends ParentClass
{
    /** @var int */
    public $childPublicAttribute = 4;
    /** @var int */
    protected $childProtectedAttribute = 5;
    /** @var int */
    private $childPrivateAttribute = 6;
}
