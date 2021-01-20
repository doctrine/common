<?php

namespace Doctrine\Tests\Common\Proxy;

class Php8StaticType
{
    public function foo(mixed $bar) : static
    {
        return $this;
    }

	public function fooNull(mixed $bar) : ?static
	{
		return $this;
	}
}
