<?php

namespace Doctrine\Tests\Common\Proxy;

trait IdentifierFieldTrait
{
    /** @var int */
    private $identifierFieldInTrait;

    public function getIdentifierFieldInTrait() : int
    {
        return $this->identifierFieldInTrait;
    }
}
