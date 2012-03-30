<?php

namespace Doctrine\Tests\Common\Cache;

use Doctrine\Common\Cache\Cache;

abstract class CacheTest extends \Doctrine\Tests\DoctrineTestCase
{
    public function testBasics()
    {
        $cache = $this->_getCacheDriver();

        // Test save
        $cache->save('test_key', 'testing this out');

        // Test contains to test that save() worked
        $this->assertTrue($cache->contains('test_key'));

        // Test fetch
        $this->assertEquals('testing this out', $cache->fetch('test_key'));

        // Test delete
        $cache->save('test_key2', 'test2');
        $cache->delete('test_key2');
        $this->assertFalse($cache->contains('test_key2'));

        // Fetch/save test with objects (Is cache driver serializes/unserializes objects correctly ?)
        $cache->save('test_object_key', new \ArrayObject());
        $this->assertTrue($cache->fetch('test_object_key') instanceof \ArrayObject);
    }

    public function testDeleteAll()
    {
        $cache = $this->_getCacheDriver();
        $cache->save('test_key1', '1');
        $cache->save('test_key2', '2');
        $cache->deleteAll();

        $this->assertFalse($cache->contains('test_key1'));
        $this->assertFalse($cache->contains('test_key2'));
    }

    public function testFlushAll()
    {
        $cache = $this->_getCacheDriver();
        $cache->save('test_key1', '1');
        $cache->save('test_key2', '2');
        $cache->flushAll();

        $this->assertFalse($cache->contains('test_key1'));
        $this->assertFalse($cache->contains('test_key2'));
    }

    public function testNamespace()
    {
        $cache = $this->_getCacheDriver();
        $cache->setNamespace('test_');
        $cache->save('key1', 'test');

        $this->assertTrue($cache->contains('key1'));

        $cache->setNamespace('test2_');

        $this->assertFalse($cache->contains('key1'));
    }

    public function testZeroNotFalse()
    {
        $cache = $this->_getCacheDriver();

        // Test save of int 0
        $cache->save('test_key_zero', 0);
        $cache->save('test_key_false', false);
        $cache->save('test_key_empty', '');

        // Test contains on int 0
        $this->assertTrue($cache->contains('test_key_zero'));

        // Test contains FALSE value
        $this->assertTrue($cache->contains('test_key_false'));

        // Test contains FALSE value
        $this->assertTrue($cache->contains('test_key_empty'));

        // Test fetch of int 0
        $this->assertEquals(0, $cache->fetch('test_key_zero'));

        // Test fetch FALSE value
        $this->assertFalse($cache->fetch('test_key_false'));

        // Test fetch FALSE value
        $this->assertEquals('', $cache->fetch('test_key_empty'));
    }

    /**
     * @group DCOM-43
     */
    public function testGetStats()
    {
        if ($this instanceof ArrayCacheTest || $this instanceof ZendDataCacheTest ) {
            $this->markTestSkipped("Statistics are not available for this driver");
        }

        $cache = $this->_getCacheDriver();
        $stats = $cache->getStats();


        $this->assertArrayHasKey(Cache::STATS_HITS,   $stats);
        $this->assertArrayHasKey(Cache::STATS_MISSES, $stats);
        $this->assertArrayHasKey(Cache::STATS_UPTIME, $stats);
        $this->assertArrayHasKey(Cache::STATS_MEMORY_USAGE, $stats);
        $this->assertArrayHasKey(Cache::STATS_MEMORY_AVAILIABLE, $stats);
    }

    /**
     * @return \Doctrine\Common\Cache\CacheProvider
     */
    abstract protected function _getCacheDriver();

    /**
     * Clean-up
     */
    protected function tearDown()
    {
        if (0 != $this->getStatus()) {
            return;
        }

        $cache = $this->_getCacheDriver();

        $cache->delete('test_key');
        $cache->delete('test_key1');
        $cache->delete('test_key2');
        $cache->delete('test_object_key');
        $cache->delete('test_key_zero');
        $cache->delete('test_key_false');
        $cache->delete('test_key_empty');

        $cache->setNamespace('test_');
        $cache->delete('key1');
        $cache->setNamespace('test2_');
        $cache->delete('key1');
    }
}