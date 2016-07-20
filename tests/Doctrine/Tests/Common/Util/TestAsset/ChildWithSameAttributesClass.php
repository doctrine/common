<?php

namespace Doctrine\Tests\Common\Util\TestAsset;

final class ChildWithSameAttributesClass extends ParentClass
{
    public $parentPublicAttribute = 4;
    protected $parentProtectedAttribute = 5;
    private $parentPrivateAttribute = 6;
}
