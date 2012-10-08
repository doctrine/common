<?php

namespace Doctrine\Tests\Common\Cache;

use Doctrine\Common\Cache\SQLite;

class SQLiteTest extends CacheTest
{
    public static $path;

    public static function setUpBeforeClass()
    {
        static::$path = sys_get_temp_dir().'/doctrine-common.db';
    }

    public static function tearDownAfterClass()
    {
        @unlink(static::$path);
    }

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
        return new SQLite(static::$path);
    }
}
