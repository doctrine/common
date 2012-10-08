<?php

namespace Doctrine\Tests\Common\Cache;

use Doctrine\Common\Cache\SQLiteCache;

class SQLiteCacheTest extends CacheTest
{
    public function testGetStats()
    {
        $cache = $this->_getCacheDriver();

        $this->assertNull($cache->getStats());
    }

    protected function setup()
    {
        if (!class_exists('SQLite3')) {
            $this->markTestSkipped('The ' . __CLASS__ .' requires the use of sqlite3');
        }
    }

    /**
     * @return \Doctrine\Common\Cache\CacheProvider
     */
    protected function _getCacheDriver()
    {
        return new SQLiteCache(':memory');
    }
}
