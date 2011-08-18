<?php

namespace Doctrine\Tests\Common\Cache;

use Doctrine\Common\Cache\MemcachedCache;

class MemcachedCacheTest extends CacheTest
{
    private $_memcached;

    public function setUp()
    {
        if (extension_loaded('memcached')) {
            $this->_memcached = new \Memcached();
            $this->_memcached->setOption(\Memcached::OPT_COMPRESSION, false);
            $ok = $this->_memcached->addServer('localhost', 11211);
            if (!$ok) {
                $this->markTestSkipped('The ' . __CLASS__ .' requires at least one instance of a memcache server up and running!');
            }
        } else {
            $this->markTestSkipped('The ' . __CLASS__ .' requires the extension php-memcached to be installed on the system!');
        }
    }

    protected function _getCacheDriver()
    {
        $driver = new MemcachedCache();
        $driver->setMemcached($this->_memcached);
        return $driver;
    }
}