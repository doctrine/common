<?php

namespace Doctrine\Tests\Common\Cache;

use Doctrine\Common\Cache\CacheNamespace;
use Doctrine\Common\Cache\ArrayCache;

class CacheNamespaceTest extends \PHPUnit_Framework_TestCase
{
    public function testKeysDoNotInterfere()
    {
        $cache = new ArrayCache;

        $ns = new CacheNamespace('foo', $cache);
        $ns->save('foo', 'bar');

        $this->assertFalse($cache->fetch('foo'));

        $this->assertEquals('bar', $ns->fetch('foo'));
    }

    public function testIncrementVersionInvalidatesKeys()
    {
        $cache = new ArrayCache;

        $ns = new CacheNamespace('foo', $cache);
        $ns->save('foo', 'bar');

        $this->assertEquals('bar', $ns->fetch('foo'));

        $ns->incrementNamespaceVersion();

        $this->assertFalse($ns->fetch('foo'));
    }
}
