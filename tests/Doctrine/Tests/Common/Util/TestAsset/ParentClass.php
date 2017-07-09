<?php

declare(strict_types=1);

namespace Doctrine\Tests\Common\Util\TestAsset;

abstract class ParentClass
{
    public $parentPublicAttribute = 1;
    protected $parentProtectedAttribute = 2;
    private $parentPrivateAttribute = 3;
}
