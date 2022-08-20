<?php
declare(strict_types=1);

namespace Doctrine\Tests\Common\Proxy;

class InvokationSpy
{
    public $invokations = [];

    public function __invoke($proxy, $method, $parameters)
    {
        $this->invokations[] = [$proxy, $method, $parameters];
    }
}
