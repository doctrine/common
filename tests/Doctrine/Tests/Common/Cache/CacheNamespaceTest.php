<?php

namespace CHH\Silex\Test;

use Doctrine\Common\Cache\CacheNamespace;
use Doctrine\Common\Cache\ArrayCache;

class CacheNamespaceTest extends \PHPUnit_Framework_TestCase
{
    function testKeysDoNotInterfere()
    {
        $cache = new ArrayCache;

        $ns = new CacheNamespace('foo', $cache);
        $ns->save('foo', 'bar');

        $this->assertFalse($cache->fetch('foo'));

        $this->assertEquals('bar', $ns->fetch('foo'));
    }

    function testIncrementVersionInvalidatesKeys()
    {
        $cache = new ArrayCache;

        $ns = new CacheNamespace('foo', $cache);
        $ns->save('foo', 'bar');

        $this->assertEquals('bar', $ns->fetch('foo'));

        $ns->incrementNamespaceVersion();

        $this->assertFalse($ns->fetch('foo'));
    }
}
