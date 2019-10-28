<?php

namespace Doctrine\Tests\Common\Proxy;

final class ValueId
{
    /** @var string */
    private $id;

    public function __construct(string $id)
    {
        $this->id = $id;
    }

    public static function new(string $id) : self
    {
        return new self($id);
    }
}
