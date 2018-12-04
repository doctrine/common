<?php

namespace Doctrine\Tests\Common\Proxy;

trait IdentifierFieldTrait
{

    /**
     * @var int
     */
    private $identifierFieldInTrait;

    /**
     * @return int
     */
    public function getIdentifierFieldInTrait(): int
    {
        return $this->identifierFieldInTrait;
    }
}
