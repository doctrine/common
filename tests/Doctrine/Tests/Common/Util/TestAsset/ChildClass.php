<?php

declare(strict_types=1);

namespace Doctrine\Tests\Common\Util\TestAsset;

final class ChildClass extends ParentClass
{
    public $childPublicAttribute = 4;
    protected $childProtectedAttribute = 5;
    private $childPrivateAttribute = 6;
}
