<?php

namespace Doctrine\Tests\Common\Proxy;

trait IdentifierField
{
    /** @var int */
    private $identifierFieldInTrait;

    public function getIdentifierFieldInTrait() : int
    {
        return $this->identifierFieldInTrait;
    }
}
