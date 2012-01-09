<?php

namespace Doctrine\Tests\Common\Cache;

use Doctrine\Common\Cache\Cache;

abstract class CacheTest extends \Doctrine\Tests\DoctrineTestCase
{
    protected $cache;

    public function testBasics()
    {
        // Test save
        $this->cache->save('test_key', 'testing this out');

        // Test contains to test that save() worked
        $this->assertTrue($this->cache->contains('test_key'));

        // Test fetch
        $this->assertEquals('testing this out', $this->cache->fetch('test_key'));

        // Test delete
        $this->cache->save('test_key2', 'test2');
        $this->cache->delete('test_key2');
        $this->assertFalse($this->cache->contains('test_key2'));
    }

    public function testDeleteAll()
    {
        $this->cache->save('test_key1', '1');
        $this->cache->save('test_key2', '2');
        $this->cache->deleteAll();

        $this->assertFalse($this->cache->contains('test_key1'));
        $this->assertFalse($this->cache->contains('test_key2'));
    }

    public function testFlushAll()
    {
        $this->cache->save('test_key1', '1');
        $this->cache->save('test_key2', '2');
        $this->cache->flushAll();

        $this->assertFalse($this->cache->contains('test_key1'));
        $this->assertFalse($this->cache->contains('test_key2'));
    }

    public function testNamespace()
    {
        $this->cache->setNamespace('test_');
        $this->cache->save('key1', 'test');

        $this->assertTrue($this->cache->contains('key1'));

        $this->cache->setNamespace('test2_');

        $this->assertFalse($this->cache->contains('key1'));
    }

    public function testStore0()
    {
        $this->cache->save('key', 0);
        $this->assertTrue($this->cache->contains('key'));
        $this->assertEquals(0, $this->cache->fetch('key'));
    }

    public function testStoreNull()
    {
        $this->cache->save('key', null);
        $this->assertTrue($this->cache->contains('key'));
        $this->assertEquals(null, $this->cache->fetch('key'));
    }

    public function testStoreFalse()
    {
        $this->cache->save('key', false);
        $this->assertTrue($this->cache->contains('key'));
        $this->assertEquals(false, $this->cache->fetch('key'));
    }

    public function testStoreArray()
    {
        $value = array('foo' => 'foo', 'bar' => 'bar');
        $this->cache->save('key', $value);
        $this->assertTrue($this->cache->contains('key'));
        $this->assertEquals($value, $this->cache->fetch('key'));
    }

    public function testStoreObject()
    {
        $value = new \stdClass();
        $value->foo = 'foo';
        $value->bar = 'bar';
        $this->cache->save('key', $value);
        $this->assertTrue($this->cache->contains('key'));
        $this->assertEquals($value, $this->cache->fetch('key'));
    }

    /**
     * @group DCOM-43
     */
    public function testGetStats()
    {
        $stats = $this->cache->getStats();

        $this->assertArrayHasKey(Cache::STATS_HITS,   $stats);
        $this->assertArrayHasKey(Cache::STATS_MISSES, $stats);
        $this->assertArrayHasKey(Cache::STATS_UPTIME, $stats);
        $this->assertArrayHasKey(Cache::STATS_MEMORY_USAGE, $stats);
        $this->assertArrayHasKey(Cache::STATS_MEMORY_AVAILIABLE, $stats);
    }

    protected function setUp()
    {
        parent::setUp();
        $this->cache = $this->_getCacheDriver();
        $this->cache->flushAll();
    }

    protected function tearDown()
    {
        parent::tearDown();
        $this->cache = null;
    }

    /**
     * @return \Doctrine\Common\Cache\CacheProvider
     */
    abstract protected function _getCacheDriver();
}